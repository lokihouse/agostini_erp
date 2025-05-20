<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker; // Import Faker Factory

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->command->warn('Nenhuma empresa encontrada. Crie empresas antes de popular os veículos.');
            return;
        }

        $this->command->info('Criando veículos para as empresas...');

        foreach ($companies as $company) {
            $this->command->line(" -> Para a empresa: {$company->name}");

            // Cria 3 a 5 veículos para cada empresa
            $numberOfVehicles = rand(3, 5);

            for ($i = 0; $i < $numberOfVehicles; $i++) {
                $plate = $this->generateUniqueLicensePlateForCompany($company->uuid);

                if ($plate) {
                    Vehicle::create([
                        'uuid' => Str::uuid()->toString(),
                        'company_id' => $company->uuid,
                        'license_plate' => $plate,
                        'description' => $this->generateVehicleDescription(),
                        'brand' => $this->generateRandomBrand(),
                        'model_name' => $this->generateRandomModel(),
                        'year_manufacture' => rand(2010, date('Y')),
                        'year_model' => rand(2010, date('Y') + 1),
                        'color' => $this->generateRandomColor(), // This method is now defined below
                        'cargo_volume_m3' => rand(0, 1) == 1 ? $this->faker()->randomFloat(3, 0.5, 30) : null,
                        'max_load_kg' => rand(0, 1) == 1 ? $this->faker()->randomFloat(2, 100, 20000) : null,
                        'renavam' => rand(0, 1) == 1 ? $this->faker()->unique()->numerify('###########') : null,
                        'is_active' => $this->faker()->boolean(90),
                        'notes' => $this->faker()->optional(0.2)->sentence,
                    ]);
                    $this->command->line("    - Criado veículo com placa: {$plate}");
                } else {
                    $this->command->warn("    - Não foi possível gerar placa única para a empresa {$company->name} após várias tentativas.");
                }
            }
        }

        $this->command->info('Veículos criados.');
    }

    /**
     * Gera uma placa de veículo única para uma empresa.
     */
    protected function generateUniqueLicensePlateForCompany(string $companyId, int $maxAttempts = 10): ?string
    {
        $faker = Faker::create('pt_BR');
        for ($i = 0; $i < $maxAttempts; $i++) {
            $plate = rand(0, 1) == 1
                ? $faker->regexify('[A-Z]{3}[0-9][A-Z][0-9]{2}') // Mercosul
                : $faker->regexify('[A-Z]{3}-[0-9]{4}'); // Antigo

            $exists = Vehicle::where('company_id', $companyId)
                ->where('license_plate', $plate)
                ->exists();

            if (!$exists) {
                return $plate;
            }
        }

        return null;
    }

    /**
     * Gera uma descrição de veículo aleatória.
     */
    protected function generateVehicleDescription(): string
    {
        $faker = Faker::create('pt_BR');
        $types = ['Caminhão', 'Van', 'Utilitário', 'Moto', 'Carro'];
        $colors = ['Branco', 'Preto', 'Prata', 'Vermelho', 'Azul', 'Verde'];
        $type = $faker->randomElement($types);
        $color = $faker->randomElement($colors);
        return "{$type} {$color} - Entrega";
    }

    /**
     * Gera uma marca de veículo aleatória.
     */
    protected function generateRandomBrand(): string
    {
        $faker = Faker::create('pt_BR');
        $brands = ['Fiat', 'Volkswagen', 'Chevrolet', 'Ford', 'Renault', 'Honda', 'Toyota', 'Mercedes-Benz', 'Volvo', 'Scania'];
        return $faker->randomElement($brands);
    }

    /**
     * Gera um modelo de veículo aleatório.
     */
    protected function generateRandomModel(): string
    {
        $faker = Faker::create('pt_BR');
        $models = ['Uno', 'Gol', 'Onix', 'Ka', 'Kwid', 'Civic', 'Corolla', 'Sprinter', 'FH', 'R450', 'CG 160', 'Fiorino', 'Saveiro'];
        return $faker->randomElement($models);
    }

    /**
     * Gera uma cor de veículo aleatória.
     */
    protected function generateRandomColor(): string
    {
        $faker = Faker::create('pt_BR');
        $colors = ['Branco', 'Preto', 'Prata', 'Vermelho', 'Azul', 'Verde', 'Cinza', 'Amarelo']; // Adicionei mais algumas cores
        return $faker->randomElement($colors);
    }


    // Se precisar usar o Faker dentro dos métodos auxiliares, injete-o ou crie uma instância
    protected function faker()
    {
        // Re-instantiate or ensure a single instance if performance is critical,
        // but for seeders, creating new instances in helper methods is usually fine.
        return Faker::create('pt_BR');
    }
}
