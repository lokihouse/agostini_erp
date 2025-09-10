<?php

namespace App\Http\Controllers;

use App\Models\PricingTable;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // import do DomPDF

class PricingTablePdfController extends Controller
{
    public function generatePdf()
    {
        $pricingTables = PricingTable::with('product')->get();

        $pdf = Pdf::loadView('pdf.pricing_table_pdf', compact('pricingTables'))
                  ->setPaper('a4', 'landscape'); // papel A4, orientação paisagem

        return $pdf->download('precificacao.pdf'); // força download do PDF
    }
}
