<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $users = \App\Models\User::where('company_id', $companyId)->get();

        return response() ->json($users);
     
    }

     public function store(Request $request)
    {
        // Valida os dados enviados pelo usuário
        $data = $request->validate([
            'company_id' => 'required|uuid|exists:companies,uuid', // obrigatório e deve existir
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:8',
            'is_active' => 'required|boolean'
        ]);

        // Criptografa a senha antes de salvar
        $data['password'] = bcrypt($data['password']);

        // Cria o usuário
        $user = User::create($data);

        return response()->json($user, 201);
    }


    public function show(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $user = User::with('company')
            ->where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($user);
    }
     
    public function update(Request $request, string $uuid)
    {
       $companyId = $request->user()->company_id;

        $user = User::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $request->validate([
            'company_id'=> 'required|uuid|exists:companies,uuid',
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                rule::unique('users')->ignore($uuid, 'uuid') //<- aqui ignora o usuário atual
            ],
            'password' => 'required|string|min:8',
            'is_active' => 'required|boolean'
        ]);

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $user = User::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();
            
        $user->delete();
        return response()->json(['message'=> 'Funcionário excluído com sucesso']);
    }

}