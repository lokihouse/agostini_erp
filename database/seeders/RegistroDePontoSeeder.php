<?php

namespace Database\Seeders;

use App\Http\Controllers\RegistroDePontoController;
use App\Models\User;
use Illuminate\Database\Seeder;

class RegistroDePontoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            $data_inicial = new \DateTime("2024-03-01 00:00");
            $data_final = new \DateTime("2024-03-24 00:00");
            $latitude = -21.0091567;
            $longitude = -42.8417787;

            while ($data_inicial < $data_final) {
                $data_inicial = $this->setData($data_inicial, 1);

                RegistroDePontoController::registrar(
                    user_id: $user->id,
                    dia: $data_inicial->format('Y-m-d'),
                    hora: $data_inicial->format('H:i'),
                    latitude: $latitude + fake()->randomFloat(8, -0.001, 0.001),
                    longitude: $longitude + fake()->randomFloat(8, -0.001, 0.001),
                    ip: fake()->ipv4()
                );

                $data_inicial = $this->setData($data_inicial, 2);

                RegistroDePontoController::registrar(
                    user_id: $user->id,
                    dia: $data_inicial->format('Y-m-d'),
                    hora: $data_inicial->format('H:i'),
                    latitude: $latitude + fake()->randomFloat(8, -0.001, 0.001),
                    longitude: $longitude + fake()->randomFloat(8, -0.001, 0.001),
                    ip: fake()->ipv4()
                );

                $data_inicial = $this->setData($data_inicial, 3);

                RegistroDePontoController::registrar(
                    user_id: $user->id,
                    dia: $data_inicial->format('Y-m-d'),
                    hora: $data_inicial->format('H:i'),
                    latitude: $latitude + fake()->randomFloat(8, -0.001, 0.001),
                    longitude: $longitude + fake()->randomFloat(8, -0.001, 0.001),
                    ip: fake()->ipv4()
                );

                $data_inicial = $this->setData($data_inicial, 4);

                RegistroDePontoController::registrar(
                    user_id: $user->id,
                    dia: $data_inicial->format('Y-m-d'),
                    hora: $data_inicial->format('H:i'),
                    latitude: $latitude + fake()->randomFloat(8, -0.001, 0.001),
                    longitude: $longitude + fake()->randomFloat(8, -0.001, 0.001),
                    ip: fake()->ipv4()
                );

                $data_inicial = $data_inicial->add(\DateInterval::createFromDateString('1 day'));
                $data_inicial->setTime(0, 0);
            }
        }
    }

    public function setData($data_inicial, $etapa)
    {
        $hora = $this->getHora($etapa);
        $data_inicial->setTime($hora, $this->getMinuto($hora));
        return $data_inicial;
    }

    public function getHora(int $etapa)
    {
        return match ($etapa) {
            1 => fake()->numberBetween(6, 8),
            2 => fake()->numberBetween(11, 12),
            3 => fake()->numberBetween(13, 14),
            4 => fake()->numberBetween(17, 19),
        };
    }

    public function getMinuto(int $hora)
    {
        return match ($hora) {
             6, 11, 13, 17 => fake()->numberBetween(45,59),
             7, 18 => fake()->numberBetween(0,59),
             8, 12, 14, 19 => fake()->numberBetween(0,15),
        };
    }
}
