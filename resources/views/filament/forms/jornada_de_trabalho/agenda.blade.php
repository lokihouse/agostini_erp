@php

$show = true;

if(is_null($getRecord())) $show = false;

function agruparPorDiaDoCiclo($horarios)
{
    $agrupado = [];
    $dias = [];
    $maxRegistros = 0;

    foreach ($horarios as $horario) {
        $dia = $horario['dia_do_ciclo'];

        // Agrupar por dia do ciclo
        if (!isset($agrupado[$dia])) {
            $agrupado[$dia] = [];
            $dias[] = $dia;
        }
        $agrupado[$dia][] = $horario;

        // Verificar o número máximo de registros
        $maxRegistros = max($maxRegistros, count($agrupado[$dia]));
    }

    foreach ($agrupado as $dia => &$grupo) {
        usort($grupo, function ($a, $b) {
            return strcmp($a['entrada'], $b['entrada']);
        });
    }

    return ['horarios' => $agrupado, 'dias' => $dias, 'max_registros' => $maxRegistros];
}

if($show) $dadosAgrupados = agruparPorDiaDoCiclo($getRecord()->toArray()['horarios_de_trabalho']);

@endphp

@if($show)
<div class="relative overflow-x-auto border sm:rounded-lg">
    <table class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b">
            <tr>
                <th scope="col" class="p-2 w-1 text-center border-e">Dia do Ciclo</th>
                @for($i = 0; $i < $dadosAgrupados['max_registros']; $i++)
                    <th scope="col" class="p-2 text-center border-s">Entrada</th>
                    <th scope="col" class="p-2 text-center">Saída</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @for($j = 1; $j < value($getRecord()->dias_de_ciclo) +1; $j++)
                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                    <td class="text-center text-xs border-e">{{ $j }}</td>
                    @for($i = 0; $i < $dadosAgrupados['max_registros']; $i++)
                        @php
                        $entrada = (in_array($j, $dadosAgrupados['dias']) && isset($dadosAgrupados['horarios'][$j][$i])) ? substr($dadosAgrupados['horarios'][$j][$i]['entrada'], 0, 5) : '-';
                        $saida = (in_array($j, $dadosAgrupados['dias']) && isset($dadosAgrupados['horarios'][$j][$i])) ? substr($dadosAgrupados['horarios'][$j][$i]['saida'], 0, 5) : '-';
                        @endphp
                        <td class="p-2 text-center border-s">{{ $entrada }}</td>
                        <td class="p-2 text-center">{{ $saida }}</td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
</div>
@endif
