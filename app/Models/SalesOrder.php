<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'client_id',
        'sales_visit_id',
        'user_id',
        'order_number',
        'order_date',
        'delivery_deadline',
        'status',
        'total_amount',
        'notes',
        'cancellation_reason',
        'cancellation_details',
        'cancelled_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_deadline' => 'date',
        'total_amount' => 'decimal:2',
        'cancelled_at' => 'datetime'
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled'; // NOVO

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Rascunho',
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_PROCESSING => 'Processando',
            self::STATUS_SHIPPED => 'Enviado',
            self::STATUS_DELIVERED => 'Entregue',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
            if (empty($model->user_id) && Auth::check()) {
                $model->user_id = Auth::id();
            }
            if (empty($model->order_date)) {
                $model->order_date = Carbon::today();
            }
            if (empty($model->order_number) && $model->company_id) {
                $year = Carbon::now()->format('Y');
                $prefix = "PV-{$year}-";
                $companyLastOrder = SalesOrder::where('company_id', $model->company_id)
                    ->where('order_number', 'like', $prefix . '%')
                    ->withTrashed() // Considera também os excluídos para a sequência
                    ->orderBy('order_number', 'desc')
                    ->lockForUpdate()
                    ->first();

                $nextSequence = 1;
                if ($companyLastOrder) {
                    $lastSequence = (int)substr($companyLastOrder->order_number, strlen($prefix));
                    $nextSequence = $lastSequence + 1;
                }
                $model->order_number = $prefix . str_pad((string)$nextSequence, 4, '0', STR_PAD_LEFT);
            }
        });

        static::updating(function (SalesOrder $salesOrder) {
            $originalStatus = $salesOrder->getOriginal('status');
            $newStatus = $salesOrder->status;

            if ($originalStatus === $newStatus) {
                return;
            }

            if ($originalStatus === self::STATUS_CANCELLED) {
                throw ValidationException::withMessages(['status' => 'Um pedido cancelado não pode ter seu status alterado.']);
            }

            if ($newStatus === self::STATUS_PENDING) {
                if (!in_array($originalStatus, [self::STATUS_DRAFT, self::STATUS_PENDING])) {
                    throw ValidationException::withMessages(['status' => 'O pedido não pode voltar ao status Pendente a partir do status atual.']);
                }
            }

            if ($newStatus === self::STATUS_APPROVED) {
                if (!in_array($originalStatus, [self::STATUS_PENDING])) {
                    throw ValidationException::withMessages(['status' => 'O pedido só pode ser Aprovado a partir do status Pendente.']);
                }
                // Criação da Ordem de Produção
                if ($originalStatus === self::STATUS_PENDING) {
                    self::createProductionOrderForSalesOrder($salesOrder);
                }
            }

            // Regra 4: Se 'Processando', não pode voltar a ser 'Aprovada'.
            if ($newStatus === self::STATUS_PROCESSING) {
                if (!in_array($originalStatus, [self::STATUS_APPROVED])) {
                    throw ValidationException::withMessages(['status' => 'O pedido só pode ir para Processando a partir do status Aprovado.']);
                }
            }

            // Regra 5: Se 'Enviando', não pode voltar a ser 'Processando'.
            if ($newStatus === self::STATUS_SHIPPED) {
                if (!in_array($originalStatus, [self::STATUS_PROCESSING])) {
                    throw ValidationException::withMessages(['status' => 'O pedido só pode ir para Enviando a partir do status Processando.']);
                }
            }

            // Regra 6: Se 'Entregue', não pode voltar a ser 'Enviando'.
            if ($newStatus === self::STATUS_DELIVERED) {
                if (!in_array($originalStatus, [self::STATUS_SHIPPED])) {
                    throw ValidationException::withMessages(['status' => 'O pedido só pode ser marcado como Entregue a partir do status Enviando.']);
                }
            }

            if ($newStatus === self::STATUS_CANCELLED && $originalStatus !== self::STATUS_CANCELLED) {
                if (empty($salesOrder->cancelled_at)) { // Preenche apenas se não estiver já preenchido (pela Action, por exemplo)
                    $salesOrder->cancelled_at = now();
                }
                // Os campos cancellation_reason e cancellation_details devem ser preenchidos pela Action no Resource.
            }
        });
    }

    protected static function createProductionOrderForSalesOrder(SalesOrder $salesOrder): void
    {
        DB::transaction(function () use ($salesOrder) {
            $productionOrderData = [
                'company_id' => $salesOrder->company_id,
                'status' => 'Pendente', // Status inicial da Ordem de Produção
                'due_date' => $salesOrder->delivery_deadline,
                'notes' => "Ordem de Produção gerada automaticamente a partir do Pedido de Venda: " . $salesOrder->order_number,
                'user_uuid' => Auth::id(), // Usuário que aprovou o pedido de venda
            ];

            // Gerar número da Ordem de Produção (lógica similar à CreateProductionOrder)
            $today = Carbon::now()->format('Ymd');
            $prefix = 'OP-' . $today . '-';
            $lastInternalOrder = ProductionOrder::where('company_id', $salesOrder->company_id) // Filtrar por empresa
            ->where('order_number', 'like', $prefix . '%')
                ->withTrashed()
                ->orderBy('order_number', 'desc')
                ->lockForUpdate()
                ->first();

            $nextSequence = 1;
            if ($lastInternalOrder) {
                $lastSequence = (int)substr($lastInternalOrder->order_number, strlen($prefix));
                $nextSequence = $lastSequence + 1;
            }
            $productionOrderData['order_number'] = $prefix . str_pad((string)$nextSequence, 4, '0', STR_PAD_LEFT);

            $productionOrder = ProductionOrder::create($productionOrderData);

            foreach ($salesOrder->items as $salesItem) {
                $productionOrderItem = ProductionOrderItem::create([
                    'company_id' => $salesOrder->company_id,
                    'production_order_uuid' => $productionOrder->uuid,
                    'product_uuid' => $salesItem->product_id,
                    'quantity_planned' => $salesItem->quantity,
                    'notes' => 'Item originado do Pedido de Venda ' . $salesOrder->order_number,
                ]);

                $product = $productionOrderItem->product;

                if($product){
                    $stepUuids = $product->productionSteps()->pluck('production_steps.uuid')->all();
                    if (!empty($stepUuids)) {
                        //$productionOrderItem->productionSteps()->sync($stepUuids);
                    }
                }
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'uuid');
    }

    public function salesVisit(): BelongsTo
    {
        return $this->belongsTo(SalesVisit::class, 'sales_visit_id', 'uuid');
    }

    public function user(): BelongsTo // Usuário que criou
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'sales_order_id', 'uuid');
    }

    // Método para recalcular o total do pedido
    public function updateTotalAmount(): void
    {
        $this->total_amount = $this->items()->sum(DB::raw('quantity * final_price'));
        $this->saveQuietly(); // Salva sem disparar eventos para evitar loops
    }
}
