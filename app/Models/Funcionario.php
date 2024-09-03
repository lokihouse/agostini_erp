<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([EmpresaScope::class])]
class Funcionario extends User
{
    protected $table = 'users';

    protected $with = ['roles'];
}
