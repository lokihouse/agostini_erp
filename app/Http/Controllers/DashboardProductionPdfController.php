<?php

namespace App\Http\Controllers;

use App\Filament\Widgets\AverageProductionTimes;
use App\Filament\Widgets\PauseTimesOverview;
use App\Filament\Widgets\ProductionStatsOverview;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App; // Para instanciar widgets

class DashboardProductionPdfController extends Controller
{
    public function generatePdf()
    {
        // Instanciar os widgets para obter seus dados
        // Usamos App::make para que a injeção de dependência do Filament funcione se necessário
        $productionStatsOverview = App::make(ProductionStatsOverview::class);
        $pauseTimesOverview = App::make(PauseTimesOverview::class);
        $averageProductionTimesWidget = App::make(AverageProductionTimes::class);

        $data = [
            'productionStats' => $productionStatsOverview->getStats(),
            'pauseStats' => $pauseTimesOverview->getStats(),
            'averageProductionTimesData' => $averageProductionTimesWidget->getPdfReportData(),
            'date' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdf.dashboard_production', $data);

        // Definir orientação paisagem se o conteúdo for largo
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('dashboard_producao_'.now()->format('Ymd_His').'.pdf');
        // Ou para download direto:
        // return $pdf->download('dashboard_producao_'.now()->format('Ymd_His').'.pdf');
    }
}
