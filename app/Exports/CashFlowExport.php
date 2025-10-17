<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CashFlowExport implements FromCollection, WithHeadings
{
    protected $year;
    protected $companyId;

    public function __construct($year, $companyId)
    {
        $this->year = $year;
        $this->companyId = $companyId;
    }

    public function headings(): array
    {
        $months = collect(range(1, 12))
            ->map(fn ($m) => \Carbon\Carbon::create($this->year, $m, 1)->translatedFormat('M/Y'))
            ->toArray();

        return array_merge(['Conta'], $months);
    }

    public function collection()
    {
        // 🔹 Filtra contas apenas da empresa do usuário
        $accounts = DB::table('chart_of_accounts')
            ->where('company_id', $this->companyId)
            ->pluck('name', 'uuid');

        $rows = [];

        foreach ($accounts as $uuid => $name) {
            $row = [$name];

            foreach (range(1, 12) as $month) {
                $monthStr = \Carbon\Carbon::create($this->year, $month, 1)->format('Y-m');

                // 🔹 Filtra lançamentos apenas da empresa atual
                $val = DB::table('cash_flows')
                    ->where('company_id', $this->companyId)
                    ->where('chart_of_account_id', $uuid)
                    ->where('month', $monthStr)
                    ->sum('amount');

                $row[] = $val ?: 0;
            }

            $rows[] = $row;
        }

        return new Collection($rows);
    }
}
