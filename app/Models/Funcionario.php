<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([EmpresaScope::class])]
class Funcionario extends User
{
    protected static function boot()
    {
        parent::boot();
        static::created(function ($funcionario) {
            $role = request()->request->all()["components"][0]["updates"]["data.roles"];
            $user = User::query()->find($funcionario->id);
            $user->syncRoles($role);
        });
    }

    protected $table = 'users';

    protected $appends = ['role'];

    protected $fillable = ['empresa_id', 'name', 'email', 'password'];

    public function getRoleAttribute(){
        $user = User::query()->where('id', $this->id)->first();
        return $user->getRoleNames()->first();
    }
}
