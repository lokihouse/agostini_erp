<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        // retorna só a empresa do usuário logado
        $companyId = $request->user()->company_id;

        $company = Company::where('uuid', $companyId)->firstOrFail();

        return response()->json($company);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'socialName' => 'required|string',
            'taxNumber' => 'required|string|unique:companies,taxNumber',
            'address_zip_code'=> 'nullable|string',
            'address_street' => 'nullable|string',
            'address_number' => 'nullable|string',
            'address_complement' => 'nullable|string',
            'address_district' => 'nullable|string',
            'address_city' => 'nullable|string',
            'address_state' => 'nullable|string|max:2',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'telephone' => 'nullable|string',
        ]);

        $company = Company::create($data);

        return response()->json($company, 201);
    }

    public function show(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $company = Company::where('uuid', $uuid)
            ->where('uuid', $companyId)
            ->firstOrFail();

        return response()->json($company);
    }

    public function update(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $company = Company::where('uuid', $uuid)
            ->where('uuid', $companyId)
            ->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|string',
            'socialName' => 'sometimes|string',
            'taxNumber' => 'sometimes|string|unique:companies,taxNumber,' . $company->uuid . ',uuid',
            'address_zip_code'=> 'nullable|string',
            'address_street' => 'nullable|string',
            'address_number' => 'nullable|string',
            'address_complement' => 'nullable|string',
            'address_district' => 'nullable|string',
            'address_city' => 'nullable|string',
            'address_state' => 'nullable|string|max:2',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'telephone' => 'nullable|string',
        ]);

        $company->update($data);

        return response()->json($company);
    }

    public function destroy(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $company = Company::where('uuid', $uuid)
            ->where('uuid', $companyId)
            ->firstOrFail();

        $company->delete();

        return response()->json([
            'message' => 'Empresa removida com sucesso'
        ]);
    }
}
