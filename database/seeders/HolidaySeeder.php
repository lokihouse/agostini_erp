<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Holiday;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = Carbon::now()->year;

        // Feriados Nacionais Globais (Exemplos)
        Holiday::factory()->global('Confraternização Universal', "{$currentYear}-01-01")->create();
        Holiday::factory()->global('Sexta-feira Santa', Carbon::createFromTimestamp(easter_date($currentYear))->subDays(2)->format('Y-m-d'), 'national', false)->create(); // Data móvel
        Holiday::factory()->global('Páscoa', Carbon::createFromTimestamp(easter_date($currentYear))->format('Y-m-d'), 'national', false)->create(); // Data móvel
        Holiday::factory()->global('Tiradentes', "{$currentYear}-04-21")->create();
        Holiday::factory()->global('Dia do Trabalho', "{$currentYear}-05-01")->create();
        Holiday::factory()->global('Independência do Brasil', "{$currentYear}-09-07")->create();
        Holiday::factory()->global('Nossa Senhora Aparecida', "{$currentYear}-10-12")->create();
        Holiday::factory()->global('Finados', "{$currentYear}-11-02")->create();
        Holiday::factory()->global('Proclamação da República', "{$currentYear}-11-15")->create();
        Holiday::factory()->global('Natal', "{$currentYear}-12-25")->create();

        // Carnaval e Corpus Christi (datas móveis, exemplos como ponto facultativo)
        $easter = Carbon::createFromTimestamp(easter_date($currentYear));
        Holiday::factory()->global('Carnaval (Véspera)', $easter->copy()->subDays(48)->format('Y-m-d'), 'optional_point', false)->create();
        Holiday::factory()->global('Carnaval', $easter->copy()->subDays(47)->format('Y-m-d'), 'optional_point', false)->create();
        Holiday::factory()->global('Corpus Christi', $easter->copy()->addDays(60)->format('Y-m-d'), 'optional_point', false)->create();


        // Feriados específicos por empresa (Exemplos)
        $companies = Company::all();
        foreach ($companies as $company) {
            Holiday::factory()->forCompany($company, 'Aniversário da Cidade X', "{$currentYear}-07-15", 'municipal')->create();
            Holiday::factory()->forCompany($company, 'Ponto Facultativo Local Y', "{$currentYear}-03-20", 'optional_point', false)->create();
        }

        // Você pode adicionar mais feriados específicos ou usar o factory para criar aleatórios
        // Holiday::factory(5)->create();
    }
}
