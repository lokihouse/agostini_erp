<?php
namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PauseReason extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const TYPE_PRODUCTIVE_TIME = 'productive_time';
    public const TYPE_DEAD_TIME = 'dead_time';
    public const TYPE_MANDATORY_BREAK = 'mandatory_break';

    public static function getTypeOptions(): array
    {
        return [
            self::TYPE_PRODUCTIVE_TIME => 'Tempo Produtivo (Contabiliza na Produção)',
            self::TYPE_DEAD_TIME => 'Tempo Morto (Não Produtivo)',
            self::TYPE_MANDATORY_BREAK => 'Pausa Obrigatória (Ex: Refeição)',
        ];
    }

    public function getTypeNameAttribute(): string
    {
        return self::getTypeOptions()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'uuid');
    }

    public function getIsGlobalAttribute(): bool
    {
        return is_null($this->company_id);
    }

    protected static function booted(): void
    {
        static::creating(function (Model $model) {
        });
    }
}
