<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroDePonto extends ModelBase
{
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
