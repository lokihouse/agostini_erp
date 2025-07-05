<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\SalesVisit;
use App\Utils\StyleSheet;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FinancialReportPdfController extends Controller
{
    public Collection $reportData;
    public array $monthHeaders = [];

    protected function generateMonthHeaders(Carbon $startDate, Carbon $endDate): void
    {
        $this->monthHeaders = [];
        $currentMonth = $startDate->copy()->startOfMonth();
        $finalMonth = $endDate->copy()->startOfMonth();

        while ($currentMonth->lte($finalMonth)) {
            $this->monthHeaders[] = $currentMonth->copy();
            $currentMonth->addMonthNoOverflow();
        }
    }

    public function __invoke(Request $request)
    {
        if(is_null($request->query('start_date')) || is_null($request->query('end_date'))){
            throw new \Error('Argumentos faltantes');
        }

        $startDate = Carbon::parse($request->get('start_date'));
        $endDate = Carbon::parse($request->get('end_date'));

        $this->reportData = ChartOfAccount::query()
            ->whereNull('parent_uuid')
            ->with(['childAccounts'])
            ->orderBy('code')
            ->get();

        $this->generateMonthHeaders($startDate, $endDate);

        $data = [
            'reportData' => $this->reportData,
            'monthHeaders' => $this->monthHeaders,
            'startDate' => Carbon::parse($request->query('start_date')),
            'endDate' => Carbon::parse($request->query('end_date')),
        ];

        $pdf = Pdf::loadView("pdf.financial_report", $data)->setPaper('a3', 'landscape');
        $fileName = 'relatorio_visitas_sem_pedido_' . (now()->format('Y-m-d-H-i')) . '.pdf';
        return $pdf->stream($fileName);
    }
}

