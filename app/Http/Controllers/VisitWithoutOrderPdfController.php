<?php

namespace App\Http\Controllers;

use App\Models\SalesVisit;
use App\Utils\StyleSheet;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class VisitWithoutOrderPdfController extends Controller
{
    public function __invoke(Request $request)
    {
        if(is_null($request->query('start_date')) || is_null($request->query('end_date'))){
            throw new \Error('Argumentos faltantes');
        }

        $visits = SalesVisit::with(['client', 'assignedTo'])
            ->where('status', SalesVisit::STATUS_COMPLETED)
            ->whereNull('sales_order_id')
            ->whereBetween('visit_end_time', [$request->query('start_date'), $request->query('end_date')])
            ->get();

        $pdf = Pdf::loadView("pdf.visits_without_order", [
            'visits' => $visits,
            'date' => now()->format('d/m/Y H:i'),
        ]);
        $fileName = 'relatorio_visitas_sem_pedido_' . (now()->format('Y-m-d-H-i')) . '.pdf';
        return $pdf->stream($fileName);
    }
}

