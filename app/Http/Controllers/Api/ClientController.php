<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
       $companyId = $request->user()->company_id;

        return response()->json(
            Client::where('company_id', $companyId)->get()
        );
    }

     public function store(Request $request)
    {
        
        $companyId = $request->user()->company_id;

        $data = $request->validate([
            'company_id' => 'required|uuid|exists:companies,uuid', // obrigatório e deve existir
            //'cnpj'                 => 'required|digits:14|unique:customers,cnpj',
            'name'                 => 'string',
            'social_name'          => 'string',
            'taxNumber'            => 'string',
            'state_registration'   => 'nullable|string',
            'municipal_registration'=> 'nullable|string',
            'email'                => 'nullable|email',
            'phone_number'         => 'nullable|string',
            'website'              => 'nullable|string',
            'address_zip_code'     => 'nullable|string',
            'address_street'       => 'nullable|string',
            'address_number'       => 'nullable|string',
            'address_complement'   => 'nullable|string',
            'address_district'     => 'nullable|string',
            'address_city'         => 'nullable|string',
            'address_state'        => 'nullable|string|max:2',
            'latitude'             => 'nullable|numeric',
            'longitude'            => 'nullable|numeric',
            'status'               => 'nullable|string',
            'notes'                => 'nullable|string',        
        ]);

        $data['company_id'] = $companyId;

        $client = Client::create($data);

        return response()->json($client, 201);
    }

    public function show(Request $request, string $uuid)
    {
       
         $companyId = $request->user()->company_id;

        $client = Client::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($client);
    }
     
    public function update(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $client = Client::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $request->validate([
            'company_id' => 'required|uuid|exists:companies,uuid', // obrigatório e deve existir
            //'cnpj'                 => 'required|digits:14|unique:customers,cnpj',
            'name'                 => 'string',
            'social_name'          => 'string',
            'taxNumber'            => 'string',
            'state_registration'   => 'nullable|string',
            'municipal_registration'=> 'nullable|string',
            'email'                => 'nullable|email',
            'phone_number'         => 'nullable|string',
            'website'              => 'nullable|string',
            'address_zip_code'     => 'nullable|string',
            'address_street'       => 'nullable|string',
            'address_number'       => 'nullable|string',
            'address_complement'   => 'nullable|string',
            'address_district'     => 'nullable|string',
            'address_city'         => 'nullable|string',
            'address_state'        => 'nullable|string|max:2',
            'latitude'             => 'nullable|numeric',
            'longitude'            => 'nullable|numeric',
            'status'               => 'nullable|string',
            'notes'                => 'nullable|string',
        ]);

        $client->update($data);

        return response()->json($client);
    }

    public function destroy(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $client = Client::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $client-> delete();

        return response()->json(['message' => 'Cliente removido com sucesso' ]);
    }
}