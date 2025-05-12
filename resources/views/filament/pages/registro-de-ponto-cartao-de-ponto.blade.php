@php
    use Carbon\Carbon;
@endphp
<x-filament-panels::page>
    {{-- Mensagens de sessão (não serão impressas) --}}
    @if (session()->has('message'))
        <div
            class="no-print p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg"
            role="alert">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="no-print p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg " role="alert">
            {{ session('error') }}
        </div>
    @endif

    {{-- Botão de Impressão --}}
    <div class="absolute top-14 right-2 flex justify-end no-print">
        <div class="py-4">
            <button
                id="imprimir_relatorio"
                type="button"
                onclick="window.print()"
                class="filament-button filament-button-size-md filament-button-color-primary inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-1 -ml-1">
                    <path fill-rule="evenodd" d="M5 2.75C5 1.784 5.784 1 6.75 1h6.5c.966 0 1.75.784 1.75 1.75v3.5A2.25 2.25 0 0 1 17.25 8.5H19a1 1 0 0 1 1 1v.75a.75.75 0 0 1-1.5 0V9.5h-.055a3.252 3.252 0 0 1-3.046-2.204L15.75 6.25v2.25A3.75 3.75 0 0 1 12 12.25H8A3.75 3.75 0 0 1 4.25 8.5v-2.5L3.105 7.296A3.25 3.25 0 0 1 .055 9.5H0V8.75a1 1 0 0 1 1-1h1.75A2.25 2.25 0 0 1 5 6.25v-3.5ZM6.5 2.5v3.75a.75.75 0 0 0 .75.75h5.5a.75.75 0 0 0 .75-.75V2.5h-7Z" clip-rule="evenodd" />
                    <path d="M3.5 14A1.5 1.5 0 0 0 5 15.5h10A1.5 1.5 0 0 0 16.5 14v-1.5h-13V14Z" />
                    <path d="M2 10.5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v1.879a.75.75 0 0 1-.36.644l-2.25 1.35A2.751 2.751 0 0 1 13.25 15H6.75a2.751 2.751 0 0 1-2.14-.627l-2.25-1.35A.75.75 0 0 1 2 12.379V10.5ZM3.5 12.621l2.25 1.35A1.252 1.252 0 0 0 6.75 14.5h6.5c.491 0 .942-.284 1.14-.729l2.25-1.35V12h-13v.621Z" />
                </svg>
                Imprimir Relatório
            </button>
        </div>
    </div>

    {{-- Área Imprimível --}}
    <div id="printable-report-area" class="w-full">
        <div class="mb-4 p-4 bg-white shadow rounded-lg print:shadow-none print:border print:border-gray-300">
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
            <div class="py-8 text-xl text-center font-bold text-gray-700 bg-white shadow rounded-lg p-4 print:shadow-none print:border print:border-gray-300">
                Usuário sem jornada de trabalho definida. Por favor, contate o RH.
            </div>
        @elseif(empty($this->tabela))
            <div class="py-8 text-xl text-center font-bold text-gray-700 bg-white shadow rounded-lg p-4 print:shadow-none print:border print:border-gray-300">
                Nenhum dado para exibir no período (ou todos os dias são futuros).
            </div>
        @else
            <div class="responsive overflow-x-auto shadow rounded-lg bg-white print:shadow-none print:overflow-visible">
                <table class="w-full text-xs text-left text-gray-600 border-collapse print:border print:border-gray-300">
                    <thead class="text-[10px] print:text-[8px] text-gray-700 uppercase bg-gray-100 print:bg-gray-100">
                    <tr class="border-b border-gray-300">
                        <th rowspan="2"
                            class="px-2 py-2 w-[85px] print:w-[50px] text-center sticky left-0 bg-gray-100 z-10 print:sticky-off print:bg-gray-100">Data
                        </th>
                        <th scope="col" class="border-x border-gray-300 text-center px-1 py-1" colspan="2">
                            1º Turno
                        </th>
                        <th scope="col" class="border-x border-gray-300 text-center px-1 py-1" colspan="2">
                            2º Turno
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
                        <tr class="{{ $this->tabelaLinhaTipoClasse($dados['tipo']) . " " . $this->tabelaLinhaInconsistenciaClasse($dados['inconsistencia']) }} hover:bg-gray-50 print:break-inside-avoid">
                            <td class="px-2 py-1.5 text-center sticky left-0 z-10 bg-gray-100 {{ $this->tabelaLinhaTipoClasse($dados['tipo']) }} print:sticky-off print:bg-inherit">
                                {{ Carbon::parse($data)->translatedFormat('d/m/Y') }}
                            </td>

                            {{-- Células de Entradas/Saídas --}}
                            @for ($i = 0; $i < 4; $i++)
                                <td class="px-2 py-1.5 text-center border-x border-gray-200 dark:border-gray-700 align-middle">
                                    @if($dados['inconsistencia'])
                                        <button
                                            type="button"
                                            wire:click="$dispatch('openJustificationModal', { date: '{{ $data }}' })"
                                            class="no-print w-full h-full text-inherit hover:bg-primary-50 focus:outline-none {{ $dados['registros_display'][$i] === '-' ? 'italic text-gray-400' : '' }}"
                                            title="Justificar/Corrigir ponto para {{ Carbon::parse($data)->translatedFormat('d/m/Y') }}"
                                        >
                                            {{ $dados['registros_display'][$i] }}
                                        </button>
                                    @else
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
                            <td class="px-2 py-1.5 text-center border-r border-gray-200 font-medium {{ $dados['saldo_dia'] < 0 ? 'text-red-600 print:text-red-600' : ($dados['saldo_dia'] > 0 ? 'text-green-600 print:text-green-600' : '') }}">
                                {{ $this->formatarSaldoCustom($dados['saldo_dia']) }}
                            </td>
                            <td class="px-2 py-1.5 text-center border-r border-gray-200 font-semibold {{ $dados['saldo_acumulado'] < 0 ? 'text-red-700 print:text-red-700' : ($dados['saldo_acumulado'] > 0 ? 'text-green-700 print:text-green-700' : '') }}">
                                {{ $this->formatarSaldoCustom($dados['saldo_acumulado']) }}
                            </td>
                            <td class="px-2 py-1.5 text-left">
                                {{ implode(" | ", $dados['observacao']) }}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="text-xs print:text-[10px] font-semibold text-gray-800 bg-gray-100 print:bg-gray-100">
                    <tr class="border-t-2 border-gray-300">
                        <td colspan="5" class="px-2 py-2 text-right">TOTAIS DO PERÍODO:</td>
                        <td class="px-2 py-2 text-center border-x border-gray-300">{{ $this->formatarSaldoCustom(array_sum(array_column($this->tabela, 'chd'))) }}</td>
                        <td class="px-2 py-2 text-center border-r border-gray-300">{{ $this->formatarSaldoCustom(array_sum(array_column($this->tabela, 'cha'))) }}</td>
                        <td class="px-2 py-2 text-center border-r border-gray-300 {{ array_sum(array_column($this->tabela, 'saldo_dia')) < 0 ? 'text-red-600 print:text-red-600' : (array_sum(array_column($this->tabela, 'saldo_dia')) > 0 ? 'text-green-600 print:text-green-600' : '') }}">
                            {{ $this->formatarSaldoCustom(array_sum(array_column($this->tabela, 'saldo_dia'))) }}
                        </td>
                        <td class="px-2 py-2 text-center border-r border-gray-300 {{ ($this->tabela ? end($this->tabela)['saldo_acumulado'] : 0) < 0 ? 'text-red-700 print:text-red-700' : (($this->tabela ? end($this->tabela)['saldo_acumulado'] : 0) > 0 ? 'text-green-700 print:text-green-700' : '') }}">
                            {{ $this->formatarSaldoCustom($this->tabela ? end($this->tabela)['saldo_acumulado'] : 0) }}
                        </td>
                        <td class="px-2 py-2"></td>
                    </tr>
                    </tfoot>
                </table>
                <div class="text-[10px] p-2 text-gray-600 print:text-[8px]">
                    <div>¹ C.H.D - Carga Horária Definida (prevista para o dia).</div>
                    <div>² C.H.A - Carga Horária Aferida (efetivamente trabalhada no dia).</div>
                    <div class="mt-1">Observações: Feriados e fins de semana podem ter CHD 00:00:00 se não houver
                        trabalho previsto. Inconsistências são destacadas. Batidas manuais podem ser
                        adicionadas/corrigidas através do modal de justificativa.
                    </div>
                </div>
            </div>
        @endif
    </div> {{-- Fim da Área Imprimível --}}

    {{-- Modal de Justificativa (não será impresso) --}}
    <div class="no-print">
        @livewire('time-clock.justification-modal')
    </div>

    @push('scripts')
        {{-- Scripts (não serão impressos) --}}
        <div class="no-print">
            <script>
                document.addEventListener('livewire:initialized', () => {
                    console.log("livewire:initialized")
                    Livewire.on('justificationSaved', () => {
                        console.log("justificationSaved")
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    });
                });
            </script>
        </div>
    @endpush

    @push('styles')
        <style>
            @media print {
                body nav, #imprimir_relatorio {
                    visibility: hidden;
                }

                body main {
                    margin-top: -50px !important;
                }
            }
        </style>
    @endpush
</x-filament-panels::page>
