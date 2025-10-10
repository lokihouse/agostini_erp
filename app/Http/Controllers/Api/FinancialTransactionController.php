<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use Illuminate\Http\Request;

class FinancialTransactionController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;

        $transactions = FinancialTransaction::where('company_id', $companyId)->get();

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $userCompanyId = $request->user()->company_id;

        $data = $request->validate([
            'company_id' => "required|uuid",
            'chart_of_account_uuid' => "required|uuid",
            'description' => "nullable|string",
            'amount' => "required|numeric",
            'type' => "required|in:income,expense",
            'transaction_date' => "required|date",
            'payment_method' => "nullable|string",
            'reference_document' => "nullable|string",
            'notes' => "nullable|string",
        ]);

        // segurança: garantir que o usuário só cadastre na própria empresa
        if ($data['company_id'] !== $userCompanyId) {
            return response()->json([
                'message' => 'Você não tem permissão para registrar transações nessa empresa.'
            ], 403);
        }

        $data['user_id'] = $request->user()->uuid;

        $financialTransaction = FinancialTransaction::create($data);

        return response()->json($financialTransaction, 201);
    }

    public function show(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $financialTransaction = FinancialTransaction::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($financialTransaction);
    }

    public function update(Request $request, string $uuid)
    {
        $userCompanyId = $request->user()->company_id;

        $financialTransaction = FinancialTransaction::where('company_id', $userCompanyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $request->validate([
            'company_id' => "required|uuid",
            'chart_of_account_uuid' => "required|uuid",
            'description' => "nullable|string",
            'amount' => "required|numeric",
            'type' => "required|in:income,expense",
            'transaction_date' => "required|date",
            'payment_method' => "nullable|string",
            'reference_document' => "nullable|string",
            'notes' => "nullable|string",
        ]);

        if ($data['company_id'] !== $userCompanyId) {
            return response()->json([
                'message' => 'Você não tem permissão para alterar transações nessa empresa.'
            ], 403);
        }

        $financialTransaction->update($data);

        return response()->json($financialTransaction);
    }

    public function destroy(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $financialTransaction = FinancialTransaction::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $financialTransaction->delete();

        return response()->json([
            'message' => 'Transação financeira removida com sucesso'
        ]);
    }
}