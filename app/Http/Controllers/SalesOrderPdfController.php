<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesOrderPdfController extends Controller
{
    /**
     * Gera e retorna o PDF para um pedido de venda específico.
     *
     * @param string $uuid O UUID do SalesOrder
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(string $uuid)
    {
        $order = SalesOrder::with([
            'company',
            'client',
            'user',
            'items',
            'items.product'
        ])
            ->findOrFail($uuid);

        $pdf = Pdf::loadView('pdf.sales_order', ['order' => $order]);
        $fileName = 'pedido_venda_' . str_replace(['/', '\\', ' '], '_', $order->order_number) . '.pdf';
        return $pdf->stream($fileName);
    }
}
