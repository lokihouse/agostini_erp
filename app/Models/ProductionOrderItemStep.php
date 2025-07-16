<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionOrderItemStep extends Pivot
{
    use HasUuids;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'production_order_item_steps';

    protected $fillable = [
        'production_order_item_uuid',
        'production_step_uuid',
    ];

    public function productionOrderItem(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderItem::class, 'production_order_item_uuid', 'uuid');
    }

    public function productionStep(): BelongsTo
    {
        return $this->belongsTo(ProductionStep::class, 'production_step_uuid', 'uuid');
    }
}
