<?php

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class EmpresaFactory extends Factory
{
    public function definition(): array
    {
        $company = fake()->company();
        return [
            "cnpj" => fake()->numerify("##.###.###/####-##"),
            "razao_social" => $company . " LTDA",
            "nome_fantasia" => $company,

            "cep" => fake()->numerify("##.###-###"),
            "logradouro" => "Rua de Teste",
            "numero" => "S/N",
            "bairro" => "Centro",
            "municipio" => fake()->city,
            "uf" => fake()->randomElement([
                "AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG","PA","PB","PR","PE","PI","RJ","RN",
                "RS","RO","RR","SC","SP","SE","TO"
            ]),

            "latitude" => fake()->latitude,
            "longitude" => fake()->longitude,
        ];
    }
}
