{{-- resources/views/livewire/time-clock/justification-modal.blade.php --}}
<div>
    @if($showModal)
        <div class="fixed inset-0 z-[1999] overflow-y-auto"
             aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75"
                     aria-hidden="true" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div
                    class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6 dark:bg-gray-800">
                    <div>
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white" id="modal-title">
                            {{ $modalTitle }}
                        </h3>
                        <div class="mt-3">
                            @if(!empty($entriesForDate))
                                <div class="mb-3 text-sm text-gray-600 dark:text-gray-400">
                                    <p class="font-semibold">Batidas registradas neste dia:</p>
                                    <ul class="mt-1 space-y-1 text-xs list-disc list-inside">
                                        @foreach($entriesForDate as $entryItem)
                                            <li>
                                                {{ $entryItem['time'] }} - {{ $entryItem['type'] }}
                                                (Status: <span class="font-medium">{{ $entryItem['status_label'] }}</span>)
                                                @if($entryItem['notes'])
                                                    <span class="block pl-4 text-gray-500 dark:text-gray-500">Obs: {{ Str::limit($entryItem['notes'], 50) }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @else
                                <p class="mb-3 text-sm text-yellow-600 dark:text-yellow-400">
                                    Nenhuma batida de ponto encontrada para este dia.
                                </p>
                            @endif
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Descreva o motivo da inconsistência. Se precisar adicionar/corrigir um horário específico, preencha o campo "Horário Correto".
                            </p>
                        </div>
                    </div>
                    <form wire:submit.prevent="saveJustification" class="mt-4 space-y-4">
                        <div>
                            <label for="correct_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Horário Correto (Opcional - HH:MM)
                            </label>
                            <input type="time" wire:model.lazy="correctTime" id="correct_time" name="correct_time"
                                   class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 @error('correctTime') border-red-500 @enderror">
                            @error('correctTime') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Se preenchido, uma nova batida manual será criada com este horário e a justificativa abaixo.</p>
                        </div>

                        <div>
                            <label for="justification_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Justificativa / Observações (Obrigatório)
                            </label>
                            <textarea wire:model.defer="justificationNotes" id="justification_notes" name="justification_notes" rows="4"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500 @error('justificationNotes') border-red-500 @enderror" required></textarea>
                            @error('justificationNotes') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                            <button type="submit"
                                    class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white border border-transparent rounded-md shadow-sm bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:col-start-2 sm:text-sm dark:focus:ring-offset-gray-800">
                                Salvar
                            </button>
                            <button type="button" wire:click="closeModal"
                                    class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600 dark:focus:ring-offset-gray-800">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
