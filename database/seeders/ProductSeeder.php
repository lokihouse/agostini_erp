<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->error('Nenhuma empresa encontrada ou criada. Crie uma empresa antes de rodar o ProductSeeder.');
            return;
        }

        Product::factory()
            ->count(50)
            ->for($company)
            ->create();
    }
}

