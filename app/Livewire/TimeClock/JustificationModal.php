<?php

namespace App\Livewire\TimeClock;

use Livewire\Component;
use App\Models\TimeClockEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException as LaravelValidationException; // Alias para evitar conflito se houver outro
use Livewire\Attributes\On;

class JustificationModal extends Component
{
    public bool $showModal = false;
    public ?string $selectedDate = null;
    public string $justificationNotes = '';
    public ?string $correctTime = null;
    public array $entriesForDate = [];
    public string $modalTitle = '';

    #[On('openJustificationModal')]
    public function openModal($date)
    {
        $this->selectedDate = $date;
        $this->justificationNotes = '';
        $this->correctTime = null;
        $carbonDate = Carbon::parse($this->selectedDate);
        $this->modalTitle = 'Justificar / Corrigir Ponto - ' . $carbonDate->format('d/m/Y');
        $this->loadEntriesForJustification();
        $this->showModal = true;
        $this->resetErrorBag(); // Limpar erros de validações anteriores ao abrir
        $this->resetValidation();
    }

    protected function loadEntriesForJustification()
    {
        if (!$this->selectedDate) {
            $this->entriesForDate = [];
            return;
        }

        $carbonDate = Carbon::parse($this->selectedDate);
        $this->entriesForDate = TimeClockEntry::where('user_id', Auth::id())
            ->whereDate('recorded_at', $carbonDate)
            ->orderBy('recorded_at')
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->uuid,
                    'time' => Carbon::parse($entry->recorded_at)->format('H:i:s'),
                    'type_key' => $entry->type,
                    'type' => TimeClockEntry::getEntryTypeOptions()[$entry->type] ?? $entry->type,
                    'status' => $entry->status,
                    'status_label' => $entry->status_label,
                    'notes' => $entry->notes,
                ];
            })
            ->toArray();
    }

    public function saveJustification()
    {
        $rules = [
            'justificationNotes' => 'required|string|min:10|max:1000',
        ];
        if ($this->correctTime) {
            // Validação de formato para UX e para garantir que $this->correctTime seja válido
            $rules['correctTime'] = 'required|date_format:H:i';
        }

        // Validações primárias do formulário
        $this->validate($rules, [
            'correctTime.date_format' => 'O campo horário correto deve estar no formato HH:MM (ex: 09:30).'
        ]);

        if ($this->selectedDate) {
            $carbonDate = Carbon::parse($this->selectedDate);
            $user = Auth::user();
            $operationSuccessMessage = 'Justificativa e/ou correção salva com sucesso!';

            // 1. Se um horário correto foi informado, tentar criar uma nova batida manual
            if ($this->correctTime) {
                $dateTimeToRecord = $carbonDate->copy()->setTimeFromTimeString($this->correctTime . ':00');

                try {
                    TimeClockEntry::create([
                        'user_id' => $user->uuid,
                        'company_id' => $user->company_id,
                        'recorded_at' => $dateTimeToRecord,
                        'type' => TimeClockEntry::TYPE_MANUAL_ENTRY,
                        'status' => TimeClockEntry::STATUS_JUSTIFIED,
                        'notes' => "REGISTRO MANUAL (Horário corrigido/adicionado pelo usuário em " . Carbon::now()->format('d/m/Y H:i') . "):\n" . $this->justificationNotes,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);
                } catch (LaravelValidationException $e) {
                    // Se a exceção de validação do modelo for capturada
                    // e contiver um erro para 'recorded_at' (que é a nossa validação de unicidade),
                    // vamos mapeá-lo para 'correctTime' para exibição no formulário.
                    if (isset($e->errors()['recorded_at'])) {
                        foreach ($e->errors()['recorded_at'] as $message) {
                            $this->addError('correctTime', $message);
                        }
                    } else {
                        // Para outros erros de validação que possam surgir do modelo,
                        // adiciona-os ao error bag com suas chaves originais.
                        // O Livewire pode já fazer isso, mas ser explícito não prejudica.
                        foreach ($e->errors() as $key => $messages) {
                            foreach ($messages as $message) {
                                $this->addError($key, $message);
                            }
                        }
                    }
                    return; // Interrompe a execução se houver erro de validação na criação
                }
            }

            // 2. Atualizar as batidas existentes do dia com a justificativa
            $justificationTextForExisting = "Justificativa do usuário (" . Carbon::now()->format('d/m/Y H:i') . "): " . $this->justificationNotes;
            $entriesToUpdate = TimeClockEntry::where('user_id', $user->uuid)
                ->whereDate('recorded_at', $carbonDate)
                ->get();

            if (!$this->correctTime && $entriesToUpdate->isEmpty()) {
                // Caso especial: justificativa para um dia sem batidas e sem novo horário informado.
                $operationSuccessMessage = 'Justificativa para o dia salva. Nenhuma batida foi criada ou alterada pois não existem registros para este dia e nenhum horário específico foi informado.';
            } else {
                foreach ($entriesToUpdate as $entry) {
                    $entry->notes = ($entry->notes ? $entry->notes . "\n---\n" : '') . $justificationTextForExisting;
                    if (in_array($entry->status, [TimeClockEntry::STATUS_NORMAL, TimeClockEntry::STATUS_ALERT])) {
                        $entry->status = TimeClockEntry::STATUS_JUSTIFIED;
                    }
                    // A validação de unicidade no modelo (hook 'updating') será chamada aqui.
                    // É improvável que cause problemas se apenas 'notes' e 'status' estão mudando.
                    $entry->save();
                }
            }

            session()->flash('message', $operationSuccessMessage);
            $this->closeModalAndRefresh();
        }
    }

    private function closeModalAndRefresh()
    {
        $this->closeModal();
        $this->dispatch('justificationSaved'); // Evento para a página principal recarregar
        // A mensagem flash já foi definida pelo método chamador (saveJustification)
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDate = null;
        $this->justificationNotes = '';
        $this->correctTime = null;
        $this->entriesForDate = [];
        $this->modalTitle = '';
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.time-clock.justification-modal');
    }
}
