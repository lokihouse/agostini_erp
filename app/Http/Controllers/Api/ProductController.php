<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
       $companyId = $request->user()->company_id;

        return response()->json(
            Product::where('company_id', $companyId)->get()
        );
    }

     public function store(Request $request)
    {
        
        $companyId = $request->user()->company_id;

        $data = $request->validate([
            //'company_id' => 'required|uuid|exists:companies,uuid', // obrigatório e deve existir
            'name' => 'required|string',
            'sku' => 'nullable|string|unique:products,sku',
            'description' => 'nullable|string',
            'stock' => 'nullable|numeric',
            'unit_of_measure' => 'required|string',
            'standart_cost' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'minimum_sale_price' => 'nullable|numeric'
        ]);

        $data['company_id'] = $companyId;

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function show(Request $request, string $uuid)
    {
       
         $companyId = $request->user()->company_id;

        $product = Product::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        return response()->json($product);
    }
     
    public function update(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $product = Product::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $data = $request->validate([
            //'company_id' => 'required|uuid|exists:companies,uuid', // obrigatório e deve existir
            'name' => 'required|string',
            'sku' =>  [
                'required',
                'string',
                rule::unique('products', 'sku')->ignore($uuid, 'uuid') //<- aqui ignora o usuário atual
            ],
            'description' => 'nullable|string',
            'stock' => 'nullable|numeric',
            'unit_of_measure' => 'required|string',
            'standart_cost' => 'nullable|numeric',
            'sale_price' => 'nullable|numeric',
            'minimum_sale_price' => 'nullable|numeric'
        ]);

        $product->update($data);

        return response()->json($product);
    }

    public function destroy(Request $request, string $uuid)
    {
        $companyId = $request->user()->company_id;

        $product = Product::where('company_id', $companyId)
            ->where('uuid', $uuid)
            ->firstOrFail();

        $product-> delete();

        return response()->json(['message' => 'Produto removido com sucesso' ]);
    }
}