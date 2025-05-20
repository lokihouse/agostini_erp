<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\TransportOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TransportOrderPdfController extends Controller
{
    public function generatePdf(string $uuid)
    {
        $transportOrder = TransportOrder::where('uuid', $uuid)
            ->with(['items.client', 'items.product', 'vehicle', 'driver'])
            ->firstOrFail();

        $pdf = Pdf::loadView("pdf.transport_order_shipment", ['transportOrder' => $transportOrder]);
        $fileName = 'documento_carga_' . ($transportOrder->transport_order_number ?? $uuid) . '.pdf';
        return $pdf->stream($fileName);
    }
}
