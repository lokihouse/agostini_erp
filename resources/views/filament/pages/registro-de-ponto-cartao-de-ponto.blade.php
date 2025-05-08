@php
    use Carbon\Carbon;
@endphp
<x-filament-panels::page>
    @if (session()->has('message'))
        <div
            class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg"
            role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg " role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="w-full ">
        <div class="mb-4 p-4 bg-white shadow rounded-lg">
            <div class="font-bold text-xl">Cartão de Ponto - {{ $this->currentUser->name }}
                @if(property_exists($this->currentUser, 'cpf') && $this->currentUser->cpf)
                    ({{ \App\Utils\Cpf::format($this->currentUser->cpf) }})
                @endif
            </div>
            @if($this->currentUser->company)
                <div class="font-medium text-sm text-gray-700">
                    Empresa: {{ $this->currentUser->company->socialName ?? $this->currentUser->company->name }}
                    @if($this->currentUser->company->taxNumber)
                        ({{ \App\Utils\Cnpj::format($this->currentUser->company->taxNumber) }})
                    @endif
                </div>
            @endif
            <div class="font-thin text-xs text-gray-500">
                Período: {{ $this->startDate->translatedFormat('d/m/Y') }}
                à {{ $this->endDate->translatedFormat('d/m/Y') }}
            </div>
        </div>

        @if(is_null($this->workShift))
            <div class="py-8 text-xl text-center font-bold text-gray-700 bg-white shadow rounded-lg p-4">
                Usuário sem jornada de trabalho definida. Por favor, contate o RH.
            </div>
        @elseif(empty($this->tabela))
            <div class="py-8 text-xl text-center font-bold text-gray-700 bg-white shadow rounded-lg p-4">
                Nenhum dado para exibir no período (ou todos os dias são futuros).
            </div>
        @else
            <div class="responsive overflow-x-auto shadow rounded-lg bg-white">
                <table class="w-full text-xs text-left text-gray-600 border-collapse">
                    <thead class="text-[10px] print:text-[8px] text-gray-700 uppercase bg-gray-100">
                    <tr class="border-b border-gray-300">
                        <th rowspan="2"
                            class="px-2 py-2 w-[85px] print:w-[50px] text-center sticky left-0 bg-gray-100 z-10">Data
                        </th>
                        <th scope="col" class="border-x border-gray-300 text-center px-1 py-1" colspan="2">
                            Entradas/Saídas 1
                        </th>
                        <th scope="col" class="border-x border-gray-300 text-center px-1 py-1" colspan="2">
                            Entradas/Saídas 2
                        </th>
                        <th rowspan="2" class="border-x border-gray-300 px-2 py-2 w-[76px] print:w-[50px] text-center">
                            C.H.D¹
                        </th>
                        <th rowspan="2" class="border-x border-gray-300 px-2 py-2 w-[76px] print:w-[50px] text-center">
                            C.H.A²
                        </th>
                        <th rowspan="2" class="border-x border-gray-300 px-2 py-2 w-[76px] print:w-[50px] text-center">
                            Saldo Dia
                        </th>
                        <th rowspan="2" class="border-x border-gray-300 px-2 py-2 w-[76px] print:w-[50px] text-center">
                            Saldo Acum.
                        </th>
                        <th rowspan="2" class="border-l border-gray-300 px-2 py-2 text-center min-w-[150px]">
                            Observações
                        </th>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <th scope="col" class="border-x border-gray-300 px-1 py-1 w-[76px] print:w-[50px] text-center">
                            1ª Ent.
                        </th>
                        <th scope="col" class="border-x border-gray-300 px-1 py-1 w-[76px] print:w-[50px] text-center">
                            1ª Saí.
                        </th>
                        <th scope="col" class="border-x border-gray-300 px-1 py-1 w-[76px] print:w-[50px] text-center">
                            2ª Ent.
                        </th>
                        <th scope="col" class="border-x border-gray-300 px-1 py-1 w-[76px] print:w-[50px] text-center">
                            2ª Saí.
                        </th>
                    </tr>
                    </thead>
                    <tbody class="text-[10px] print:text-[8px] divide-y divide-gray-200">
                    @foreach($this->tabela as $data => $dados)
                        <tr class="{{ $this->tabelaLinhaTipoClasse($dados['tipo']) . " " . $this->tabelaLinhaInconsistenciaClasse($dados['inconsistencia']) }} hover:bg-gray-50">
                            <td class="px-2 py-1.5 text-center sticky left-0 z-10 bg-gray-100 {{ $this->tabelaLinhaTipoClasse($dados['tipo']) }}">
                                {{ Carbon::parse($data)->translatedFormat('d/m/Y') }}
                            </td>

                            {{-- Células de Entradas/Saídas --}}
                            @for ($i = 0; $i < 4; $i++)
                                <td class="px-2 py-1.5 text-center border-x border-gray-200 dark:border-gray-700 align-middle">
                                    {{-- MODIFICADA A CONDIÇÃO @if --}}
                                    @if($dados['inconsistencia'] && $dados['pode_justificar'])
                                        <button type="button"
                                                wire:click="$dispatch('openJustificationModal', { date: '{{ $data }}' })"
                                                class="w-full h-full text-inherit hover:bg-primary-50 dark:hover:bg-primary-700/50 focus:outline-none {{ $dados['registros_display'][$i] === '-' ? 'italic text-gray-400 dark:text-gray-500' : '' }}"
                                                title="Justificar/Corrigir ponto para {{ Carbon::parse($data)->translatedFormat('d/m/Y') }}">
                                            {{ $dados['registros_display'][$i] }}
                                        </button>
                                    @else
                                        {{-- Se não pode justificar ou não há inconsistência, apenas mostra o valor --}}
                                        <span
                                            class="{{ $dados['registros_display'][$i] === '-' ? 'italic text-gray-400 dark:text-gray-500' : '' }}">
                                            {{ $dados['registros_display'][$i] }}
                                        </span>
                                    @endif
                                </td>
                            @endfor
                            {{-- Fim Células de Entradas/Saídas --}}

                            <td class="px-2 py-1.5 text-center border-r border-gray-200">
                                {{ $this->formatarSaldoCustom($dados['chd']) }}
                            </td>
                            <td class="px-2 py-1.5 text-center border-r border-gray-200">
                                {{ $dados['inconsistencia'] && $dados['cha'] == 0 && $dados['registros_display'][0] === '-' ? '-' : $this->formatarSaldoCustom($dados['cha']) }}
                            </td>
                            <td class="px-2 py-1.5 text-center border-r border-gray-200 font-medium {{ $dados['saldo_dia'] < 0 ? 'text-red-600' : ($dados['saldo_dia'] > 0 ? 'text-green-600' : '') }}">
                                {{ $this->formatarSaldoCustom($dados['saldo_dia']) }}
                            </td>
                            <td class="px-2 py-1.5 text-center border-r border-gray-200 font-semibold {{ $dados['saldo_acumulado'] < 0 ? 'text-red-700' : ($dados['saldo_acumulado'] > 0 ? 'text-green-700' : '') }}">
                                {{ $this->formatarSaldoCustom($dados['saldo_acumulado']) }}
                            </td>
                            <td class="px-2 py-1.5 text-left">
                                {{ implode(" | ", $dados['observacao']) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="text-xs print:text-[10px] font-semibold text-gray-800 bg-gray-100">
                    <tr class="border-t-2 border-gray-300">
                        <td colspan="5" class="px-2 py-2 text-right">TOTAIS DO PERÍODO:</td>
                        <td class="px-2 py-2 text-center border-x border-gray-300">{{ $this->formatarSaldoCustom(array_sum(array_column($this->tabela, 'chd'))) }}</td>
                        <td class="px-2 py-2 text-center border-r border-gray-300">{{ $this->formatarSaldoCustom(array_sum(array_column($this->tabela, 'cha'))) }}</td>
                        <td class="px-2 py-2 text-center border-r border-gray-300 {{ array_sum(array_column($this->tabela, 'saldo_dia')) < 0 ? 'text-red-600' : (array_sum(array_column($this->tabela, 'saldo_dia')) > 0 ? 'text-green-600' : '') }}">
                            {{ $this->formatarSaldoCustom(array_sum(array_column($this->tabela, 'saldo_dia'))) }}
                        </td>
                        <td class="px-2 py-2 text-center border-r border-gray-300 {{ ($this->tabela ? end($this->tabela)['saldo_acumulado'] : 0) < 0 ? 'text-red-700' : (($this->tabela ? end($this->tabela)['saldo_acumulado'] : 0) > 0 ? 'text-green-700' : '') }}">
                            {{ $this->formatarSaldoCustom($this->tabela ? end($this->tabela)['saldo_acumulado'] : 0) }}
                        </td>
                        <td class="px-2 py-2"></td>
                    </tr>
                    </tfoot>
                </table>
                <div class="text-[10px] p-2 text-gray-600">
                    <div>¹ C.H.D - Carga Horária Definida (prevista para o dia).</div>
                    <div>² C.H.A - Carga Horária Aferida (efetivamente trabalhada no dia).</div>
                    <div class="mt-1">Observações: Feriados e fins de semana podem ter CHD 00:00:00 se não houver
                        trabalho previsto. Inconsistências são destacadas. Batidas manuais podem ser
                        adicionadas/corrigidas através do modal de justificativa.
                    </div>
                </div>
            </div>
        @endif
    </div>
    @livewire('time-clock.justification-modal')

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('justificationSaved', () => {
                    setTimeout(() => {
                        // Livewire.dispatchTo('nome-do-componente-da-pagina', 'refreshTimeCard');
                        window.location.reload();
                    }, 1500);
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
