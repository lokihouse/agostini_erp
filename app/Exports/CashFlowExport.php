<?php

namespace App\Exports;

use App\Models\ChartOfAccount;
use App\Models\CashFlow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CashFlowExport
{
    protected int $year;
    protected string $companyId;

    public function __construct(int $year, string $companyId)
    {
        $this->year = $year;
        $this->companyId = $companyId;
    }

    public function download()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Fluxo {$this->year}");

        // 🧭 Configurações gerais
        $sheet->getDefaultColumnDimension()->setWidth(16);
        $sheet->getDefaultRowDimension()->setRowHeight(20);

        // 🧾 Cabeçalho principal
        $sheet->setCellValue('A1', "Fluxo de Caixa {$this->year}");
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 🗓️ Cabeçalho das colunas
        $sheet->setCellValue('A2', 'Plano de Contas');
        $sheet->setCellValue('B2', 'Meta');
        for ($m = 1; $m <= 12; $m++) {
            $col = Coordinate::stringFromColumnIndex($m + 2);
            $sheet->setCellValue("{$col}2", Carbon::create($this->year, $m, 1)->translatedFormat('M/Y'));
        }

        $sheet->getStyle('A2:N2')->getFont()->setBold(true);
        $sheet->getStyle('A2:N2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:N2')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFEFEFEF');

        // 📊 Buscar dados
        $accounts = ChartOfAccount::where('company_id', $this->companyId)->orderBy('code')->get();
        $cashFlows = CashFlow::where('company_id', $this->companyId)
            ->whereYear('created_at', $this->year)
            ->get()
            ->groupBy('chart_of_account_id');

        $getSum = function ($accountUuid, $month, $category = null) use ($cashFlows) {
            $flows = $cashFlows[$accountUuid] ?? collect();
            $filtered = $flows->filter(fn($f) => $f->month === $month);
            if ($category) {
                $filtered = $filtered->where('category', $category);
            }
            return (float) $filtered->sum('amount');
        };

        // 📈 Inserir linhas
        $row = 3;
        foreach ($accounts as $acc) {
            $sheet->setCellValue("A{$row}", "{$acc->code} - {$acc->name}");
            $meta = $getSum($acc->uuid, 'goal', 'goal');
            $sheet->setCellValue("B{$row}", $meta);

            for ($m = 1; $m <= 12; $m++) {
                $monthStr = Carbon::create($this->year, $m, 1)->format('Y-m');
                $col = Coordinate::stringFromColumnIndex($m + 2);
                $value = $getSum($acc->uuid, $monthStr, 'projection');
                $sheet->setCellValue("{$col}{$row}", $value);
            }

            $row++;
        }

        // 🟥 Receita em Falta
        $row++;
        $sheet->setCellValue("A{$row}", 'Receita em Falta');
        $sheet->mergeCells("A{$row}:N{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $row++;

        foreach ($accounts as $acc) {
            $meta = $getSum($acc->uuid, 'goal', 'goal');
            if ($meta <= 0) continue;

            $sheet->setCellValue("A{$row}", "{$acc->code} - {$acc->name}");
            for ($m = 1; $m <= 12; $m++) {
                $monthStr = Carbon::create($this->year, $m, 1)->format('Y-m');
                $entries = CashFlow::where('company_id', $this->companyId)
                    ->where('chart_of_account_id', $acc->uuid)
                    ->where('month', $monthStr)
                    ->whereIn('category', ['projection', 'entrada'])
                    ->sum('amount');

                $shortfall = $entries - $meta;
                $val = abs($shortfall);
                $col = Coordinate::stringFromColumnIndex($m + 2);
                $sheet->setCellValue("{$col}{$row}", $val);

                // 🎨 Cor de alerta
                $style = $sheet->getStyle("{$col}{$row}");
                if ($shortfall < 0) {
                    $style->getFont()->getColor()->setARGB('FFB00000');
                    $style->getFill()->setFillType(Fill::FILL_SOLID)
                          ->getStartColor()->setARGB('FFFDECEC');
                } elseif ($shortfall > 0) {
                    $style->getFont()->getColor()->setARGB('FF007F00');
                    $style->getFill()->setFillType(Fill::FILL_SOLID)
                          ->getStartColor()->setARGB('FFE9F7ED');
                }
            }

            $row++;
        }

        // 🧱 Bordas
        $lastRow = $row - 1;
        $sheet->getStyle("A2:N{$lastRow}")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setARGB('FFBBBBBB');

        // 💰 Formato numérico
        $sheet->getStyle("B3:N{$lastRow}")
            ->getNumberFormat()->setFormatCode('#,##0.00');

        // 🔄 Auto-ajuste de colunas
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 📤 Baixar arquivo
        $filename = "Fluxo-caixa-{$this->year}.xlsx";
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}