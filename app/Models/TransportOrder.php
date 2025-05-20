<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransportOrder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'company_id',
        'transport_order_number',
        'vehicle_id',
        'driver_id',
        'status',
        'planned_departure_datetime',
        'actual_departure_datetime',
        'planned_arrival_datetime',
        'actual_arrival_datetime',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by_user_id',
        'notes',
    ];

    protected $casts = [
        'planned_departure_datetime' => 'datetime',
        'actual_departure_datetime' => 'datetime',
        'planned_arrival_datetime' => 'datetime',
        'actual_arrival_datetime' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
            if (empty($model->transport_order_number) && $model->company_id) {
                $model->transport_order_number = static::generateOrderNumber($model->company_id);
            }
        });
    }

    public static function generateOrderNumber(string $companyId): string
    {
        $prefix = 'OT-'; // Ordem de Transporte
        $year = date('Y');
        $lastOrder = static::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->orderBy('transport_order_number', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastOrder && str_starts_with($lastOrder->transport_order_number, $prefix . $year)) {
            $lastNumber = (int) substr(strrchr($lastOrder->transport_order_number, "-"), 1);
            $nextNumber = $lastNumber + 1;
        }
        return $prefix . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'uuid');
    }

    public function driver(): BelongsTo // Motorista
    {
        return $this->belongsTo(User::class, 'driver_id', 'uuid');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id', 'uuid');
    }

    public function items(): HasMany // Itens da Ordem de Transporte (Entregas)
    {
        return $this->hasMany(TransportOrderItem::class, 'transport_order_id', 'uuid');
    }
    public function getUniqueClients()
    {
        return Client::whereIn('uuid', $this->items()->select('client_id')->distinct()->pluck('client_id'))->get();
    }
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_APPROVED => 'Aprovada',
            self::STATUS_IN_PROGRESS => 'Em Andamento',
            self::STATUS_COMPLETED => 'Concluída',
            self::STATUS_CANCELLED => 'Cancelada',
        ];
    }
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? ucfirst($this->status);
    }
}
