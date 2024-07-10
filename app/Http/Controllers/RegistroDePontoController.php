<?php

namespace App\Http\Controllers;

use App\Models\RegistroDePonto;
use App\Models\User;
use DateTime;
use Illuminate\Support\Number;

class RegistroDePontoController extends Controller
{
    public static function registrar($user_id, $dia, $hora, $latitude, $longitude, $ip, $justificativa = null)
    {
        $status = "valido";
        $motivo_status = "";
        $diasDaSemana = ['domingo', 'segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado'];
        $acoes = ['entrada_1', 'saida_1', 'entrada_2', 'saida_2', 'entrada_3', 'saida_3', 'entrada_4', 'saida_4'];
        $empresa_latitude = User::find($user_id)->empresa->latitude;
        $empresa_longitude = User::find($user_id)->empresa->longitude;
        $empresa_raio_de_registro = User::find($user_id)->empresa->raio_registro_de_ponto;
        $empresa_tolerancia = User::find($user_id)->empresa->tolerancia;
        $empresa_arredondar_tolerancia = User::find($user_id)->empresa->arredondar_tolerancia;
        $empresa_horarios = json_decode(User::find($user_id)->empresa->horario, true);

        // Dentro da cerca geográfica
        if (
            $empresa_raio_de_registro > 0 &&
            $empresa_latitude &&
            $empresa_longitude
        ) {
            if (is_null($latitude) || is_null($longitude)) {
                $status = "em analise";
                $motivo_status = "Geolocalização não disponível";
            } else {
                $distancia = self::distanciaEntreCoordenadas($empresa_latitude, $empresa_longitude, $latitude, $longitude);
                if ($distancia > $empresa_raio_de_registro) {
                    $distancia = Number::format($distancia, 0, null, 'pt_BR');
                    $status = "em analise";
                    $motivo_status = "Fora da Cerca Geográfica Digital ($distancia m)";
                }
            }
        }

        // Horário
        if ($status === "valido") {
            $diaDaSemana = $diasDaSemana[intval((new DateTime($dia))->format('w'))];

            /*$horario_entrada = array_filter($empresa_horarios, function ($el) use ($diaDaSemana) {
                return Str::startsWith($el, $diaDaSemana . "_entrada_1");
            }, ARRAY_FILTER_USE_KEY);
            $horario_saida = array_filter($empresa_horarios, function ($value, $key) use ($diaDaSemana) {
                return Str::startsWith($key, $diaDaSemana . "_saida") && $value;
            }, ARRAY_FILTER_USE_BOTH);*/
        }

        RegistroDePonto::create([
            'user_id' => $user_id,
            'dia' => $dia,
            'hora' => $hora,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'ip' => $ip,
            'status' => $status,
            'motivo_status' => $motivo_status,
            'justificativa' => $status != "valido" ? $justificativa : null
        ]);
    }

    private static function distanciaEntreCoordenadas($latitude1, $longitude1, $latitude2, $longitude2)
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) +
            (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515 * 1609.344;
        return (round($distance, 2));
    }

    public static function justificar($user_id, $dia, $hora, $justificativa)
    {
        $registro = RegistroDePonto::query()
            ->where('user_id', $user_id)
            ->where('dia', $dia)
            ->where('hora', $hora)
            ->first();

        $registro->justificativa = $justificativa;
        $registro->save();
    }
}
