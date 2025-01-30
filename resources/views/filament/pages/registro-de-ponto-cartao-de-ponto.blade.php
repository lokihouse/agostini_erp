@php

    use Illuminate\Support\Facades\DB;
    $startDate = \Carbon\Carbon::today()->startOfMonth();
    $endDate = \Carbon\Carbon::today()->endOfMonth();

    $saldo_periodo = 0;
    $saldo_dia = 0;

    $jornada = auth()->user()->jornada_de_trabalho;
    if(!is_null($jornada)){
        $jornada = $jornada->toArray();

        $calendario = \App\Models\Calendario::query()
                        ->where('empresa_id', auth()->user()->empresa_id)
                        ->orWhereNull('empresa_id')
                        ->where('data', '>=', $startDate)
                        ->where('data', '<=', $endDate)
                        ->pluck('nome', 'data')
                        ;

        $registros = \App\Models\RegistroDePonto::query()
                        ->select([DB::raw('DATE(data) as dia'), DB::raw('SUBSTRING(data, 12) as hora')])
                        ->where('user_id', auth()->user()->id)
                        ->where('data', '>=', $startDate)
                        ->where('data', '<=', $endDate)
                        ->orderBy('dia')
                        ->orderBy('hora')
                        ->get()
                        ->toArray();

        $tabela = [];

        $lastDay = null;
        for($data = \Carbon\Carbon::parse($startDate); $data <= $endDate; $data->addDay()){
            $obj = [];

            $obj['inconsistencia'] = false;
            $obj['tipo'] = 'util';
            $obj['registros'] = [];
            $obj["observacao"] = [];

            $diaDaSemana = $data->translatedFormat('l');
            if($diaDaSemana === 'sábado' || $diaDaSemana === 'domingo') $obj['tipo'] = 'final_de_semana';

            if(isset($calendario[$data->format('Y-m-d')])){
                $obj["observacao"][] = $calendario[$data->format('Y-m-d')];
                $obj['tipo'] = 'feriado';
            }

            $obj["chd"] = $obj['tipo'] === 'feriado' ? 0 : array_reduce(
                array_filter($jornada['horarios_de_trabalho'], fn($horario) => $horario['dia_do_ciclo'] === ($data->weekday() % $jornada['dias_de_ciclo'])),
                fn($older, $newer) => intval($older + \Carbon\Carbon::parse($newer['entrada'])->diff(\Carbon\Carbon::parse($newer['saida']))->totalSeconds),
                0
            );

            for ($i=0; $i < count($registros); $i++){
                if($registros[$i]['dia'] === $data->format('Y-m-d')){
                    $obj['registros'][] = $registros[$i]['hora'];
                }
            }

            if(count($obj['registros']) % 2 !== 0){
                $obj['cha'] = null;
                $obj['inconsistencia'] = true;
                $obj["observacao"][] = "Inconsistência nos registros";
            }else{
                $saldo = 0;
                for($p = 0; $p < count($obj['registros']); $p+=2){
                    $reg1 = \Carbon\Carbon::parse($obj['registros'][$p]);
                    $reg2 = \Carbon\Carbon::parse($obj['registros'][$p+1]);

                    $saldo += $reg1->diff($reg2)->totalSeconds;
                }
                $obj['cha'] = $saldo;
            }

            $obj["saldo"] = $lastDay === null ? 0 : $tabela[$lastDay]['saldo'];
            $obj["saldo"] += ($obj["cha"] ?? 0) - $obj["chd"];


            $tabela[$data->format('Y-m-d')] = $obj;
            $lastDay = $data->format('Y-m-d');
        }

        function tabelaLinhaTipoClasse($tipo) {
            switch ($tipo){
                default: return 'text-gray-900';
                case 'feriado': return 'bg-blue-50';
                case 'final_de_semana': return 'bg-red-50';
            }
        }

        function tabelaLinhaInconsistenciaClasse($inconsistencia) {
            return boolval($inconsistencia) ? 'underline font-bold' : '';
        }

        function formatarSaldo(\Carbon\CarbonInterval $saldo){
            $s = str_pad($saldo->s, 2, '0', STR_PAD_LEFT);
            $i = str_pad($saldo->i, 2, '0', STR_PAD_LEFT);
            $h = str_pad($saldo->h + $saldo->d * 24 + $saldo->m * 30 * 24, 2, '0', STR_PAD_LEFT);

            return $h . ":" . $i . ":" .$s;
        }

    }
@endphp

<x-filament-panels::page>
    <div class="w-full ">
        <div class="font-bold text-xl">Cartão de Ponto - {{ auth()->user()->nome }}
            ({{ \App\Utils\Cpf::format(auth()->user()->cpf) }})
        </div>
        <div class="font-medium text-sm">Empresa: {{ auth()->user()->empresa->razao_social }}
            ({{ \App\Utils\Cnpj::format(auth()->user()->empresa->cnpj) }})
        </div>
        <div class="font-thin text-xs">Período: {{ $startDate->translatedFormat('d/m/Y') }}
            à {{ $endDate->translatedFormat('d/m/Y') }}</div>

        @if(is_null($jornada))
            <div>
                <div class="py-8 text-xl text-center font-bold">Usuário sem jornada definida. Procure seu RH.</div>
            </div>
        @else
        <div class="responsive overflow-auto">
            <table class="w-full text-xs text-left text-gray-500 dark:text-gray-400 border">
                <thead class="text-[10px] print:text-[8px] text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border">
                <tr class="border">
                    <th rowspan="2" class="px-2 py-1.5 w-[85px] print:w-[50px] text-center">Data</th>
                    <th scope="col" class="border-s text-center" colspan="2"> Turno 1</th>
                    <th scope="col" class="border-s text-center" colspan="2"> Turno 2</th>
                    <th rowspan="2" class="border-s px-2 py-1.5  w-[76px] print:w-[50px] text-center">C.H.D¹</th>
                    <th rowspan="2" class="border-s px-2 py-1.5  w-[76px] print:w-[50px] text-center">C.H.A²</th>
                    <th rowspan="2" class="border-s px-2 py-1.5  w-[76px] print:w-[50px] text-center">Saldo</th>
                    <th rowspan="2" class="border-s px-2 py-1.5 text-center">Observações</th>
                </tr>
                <tr>
                    <th scope="col" class="border-s px-2 py-1.5 w-[76px] print:w-[50px] text-center">
                        Entrada
                    </th>
                    <th scope="col" class="border-s px-2 py-1.5 w-[76px] print:w-[50px] text-center">
                        Saída
                    </th>

                    <th scope="col" class="border-s px-2 py-1.5 w-[76px] print:w-[50px] text-center">
                        Entrada
                    </th>
                    <th scope="col" class="border-s px-2 py-1.5 w-[76px] print:w-[50px] text-center">
                        Saída
                    </th>
                </tr>
                </thead>
                <tbody class="text-[10px] print:text-[8px]">
                @foreach($tabela as $data => $dados)
                    <tr class="{{ "bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 " . tabelaLinhaTipoClasse($dados['tipo']) . " " . tabelaLinhaInconsistenciaClasse($dados['inconsistencia']) }}">
                        <td class="px-2 py-1.5 text-center">
                            {{\Carbon\Carbon::parse($data)->translatedFormat('d/m/Y')}}
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            {{ $dados['registros'][0] ?? '-' }}
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            {{ $dados['registros'][1] ?? '-' }}
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            {{ $dados['registros'][2] ?? '-' }}
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            {{ $dados['registros'][3] ?? '-' }}
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            {{ \Carbon\CarbonInterval::seconds($dados['chd'] ?? 0)->cascade()->format('%H:%I:%S') }}
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            {{ $dados['cha'] ? \Carbon\CarbonInterval::seconds($dados['cha'] ?? 0)->cascade()->format('%H:%I:%S') : '-' }}
                        </td>

                        <td class="px-2 py-1.5 text-center">
                            @if($dados['saldo'] > 0)
                                <span class="">{{ \Carbon\CarbonInterval::seconds($dados['saldo'])->cascade()->format('%H:%I:%S') }}</span>
                            @else
                                <span class="text-red-500">({{formatarSaldo(\Carbon\CarbonInterval::seconds($dados['saldo'])->cascade()) }})</span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5">
                            {{ implode(" :: ", $dados['observacao'])  }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="text-[10px]">
                <div>¹ - Carga Horária Definida</div>
                <div>² - Carga Horária Aferida</div>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
