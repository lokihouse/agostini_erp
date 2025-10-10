<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $accounts = ChartOfAccount::where('company_id', $companyId)->get();

        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id'  => 'required|uuid|exists:companies,uuid',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:asset,liability,equity,revenue,expense',
            'parent_uuid' => 'nullable|uuid|exists:chart_of_accounts,uuid',
        ]);

        // 🔹 Agora não limitamos mais ao usuário logado,
        // basta enviar o UUID da empresa no request.

        // Gerar código automático
        if (!empty($data['parent_uuid'])) {
            $parent = ChartOfAccount::where('uuid', $data['parent_uuid'])
                ->where('company_id', $data['company_id'])
                ->firstOrFail();

            $siblings = ChartOfAccount::where('company_id', $data['company_id'])
                ->where('parent_uuid', $parent->uuid)
                ->pluck('code');

            $lastNumbers = $siblings->map(function ($code) {
                $parts = explode('.', $code);
                return (int) end($parts);
            });

            $nextNumber = $lastNumbers->isEmpty() ? 1 : ($lastNumbers->max() + 1);
            $data['code'] = $parent->code . '.' . $nextNumber;

        } else {
            $siblings = ChartOfAccount::where('company_id', $data['company_id'])
                ->whereNull('parent_uuid')
                ->pluck('code');

            $nextNumber = $siblings->isEmpty() ? 1 : ($siblings->map(fn($c) => (int) $c)->max() + 1);
            $data['code'] = (string) $nextNumber;
        }

        $chartOfAccount = ChartOfAccount::create($data);

        return response()->json($chartOfAccount, 201);
    }

    public function show(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $chartOfAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($chartOfAccount);
    }

    public function update(Request $request, string $uuid)
    {
        $chartOfAccount = ChartOfAccount::where('uuid', $uuid)->firstOrFail();

        $data = $request->validate([
            'company_id'  => 'required|uuid|exists:companies,uuid',
            'code'        => 'required|string',
            'name'        => 'required|string|max:100',
            'type'        => 'required|in:asset,liability,equity,revenue,expense',
            'parent_uuid' => 'nullable|uuid|exists:chart_of_accounts,uuid',
        ]);

        if ($request->user()->company_id !== $data['company_id']) {
            return response()->json(['error' => 'Você não pode atualizar contas de outra empresa'], 403);
        }

        $chartOfAccount->update($data);

        return response()->json($chartOfAccount);
    }

    public function destroy(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $chartOfAccount = ChartOfAccount::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $chartOfAccount->delete();

        return response()->json(['message' => 'Plano de Conta removida com sucesso']);
    }
}
