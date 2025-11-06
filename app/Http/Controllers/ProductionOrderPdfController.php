<?php

namespace App\Http\Controllers;

use App\Models\ProductionOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Importar a facade do PDF

class ProductionOrderPdfController extends Controller
{
    /**
     * Gera e retorna o PDF para uma ordem de produção específica.
     *
     * @param string $uuid O UUID da ProductionOrder
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(string $uuid)
    {
        // Aumenta o limite de memória e tempo de execução para evitar erro 500 em PDFs longos
        ini_set("memory_limit", "1024M"); 
        set_time_limit(300); // 5 minutos (ajuste conforme a necessidade)
        // 1. Encontra a ordem ou falha (404)
        //    Carrega relacionamentos necessários para evitar N+1 queries na view
        $order = ProductionOrder::with([
            'company',
            'user',
            'items.product',
            'items.productionSteps'
        ])
            ->findOrFail($uuid);

        // 2. Verifica se o usuário tem permissão para ver esta ordem (IMPORTANTE!)
        //    Se você já tem o TenantScope funcionando, isso pode ser redundante,
        //    mas é uma camada extra de segurança.
        //    Descomente e ajuste se necessário:
        // if ($order->company_id !== auth()->user()->company_id) {
        //     abort(403, 'Acesso não autorizado.');
        // }

        $pdf = Pdf::loadView('pdf.production_order', ['order' => $order]);

        $fileName = 'ordem_producao_' . str_replace('/', '-', $order->order_number) . '.pdf';
        return $pdf->stream($fileName);
    }
}
