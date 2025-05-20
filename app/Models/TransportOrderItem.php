<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class TransportOrderItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_RETURNED = 'returned';

    protected $fillable = [
        'company_id',
        'transport_order_id',
        'client_id',
        'product_id',
        'sales_order_item_id',
        'quantity',
        'delivery_address_snapshot',
        'status',
        'delivery_sequence',
        'delivery_photos',
        'delivered_at',
        'returned_at',
        'return_reason',
        'processed_by_user_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'delivery_photos' => 'array',
        'delivered_at' => 'datetime',
        'returned_at' => 'datetime',
        'delivery_sequence' => 'integer',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model) {
            if (empty($model->company_id) && Auth::check() && Auth::user()->company_id) {
                $model->company_id = Auth::user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function transportOrder(): BelongsTo
    {
        return $this->belongsTo(TransportOrder::class, 'transport_order_id', 'uuid');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'uuid');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }

    public function salesOrderItem(): BelongsTo
    {
        return $this->belongsTo(SalesOrderItem::class, 'sales_order_item_id', 'uuid');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id', 'uuid');
    }
}

