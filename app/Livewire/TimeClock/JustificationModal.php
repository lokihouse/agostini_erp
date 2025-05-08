<?php

namespace App\Livewire\TimeClock;

use Livewire\Component;
use App\Models\TimeClockEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\On;

class JustificationModal extends Component
{
    public bool $showModal = false;
    public ?string $selectedDate = null;
    public string $justificationNotes = '';
    public ?string $correctTime = null; // Novo campo para o horário correto
    public array $entriesForDate = [];
    public string $modalTitle = '';

    #[On('openJustificationModal')]
    public function openModal($date)
    {
        $this->selectedDate = $date;
        $this->justificationNotes = '';
        $this->correctTime = null; // Resetar horário correto
        $carbonDate = Carbon::parse($this->selectedDate);
        $this->modalTitle = 'Justificar / Corrigir Ponto - ' . $carbonDate->format('d/m/Y');
        $this->loadEntriesForJustification();
        $this->showModal = true;
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
                    'type_key' => $entry->type, // Adicionar para lógica de tipo
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
            $rules['correctTime'] = 'required|date_format:H:i'; // Validar como HH:MM
        }

        $this->validate($rules, [
            'correctTime.date_format' => 'O campo horário correto deve estar no formato HH:MM (ex: 09:30).'
        ]);

        if ($this->selectedDate) {
            $carbonDate = Carbon::parse($this->selectedDate);
            $user = Auth::user();

            $justificationTextForExisting = "Justificativa do usuário (" . Carbon::now()->format('d/m/Y H:i') . "): " . $this->justificationNotes;

            // 1. Se um horário correto foi informado, criar uma nova batida manual
            if ($this->correctTime) {
                $dateTimeToRecord = $carbonDate->copy()->setTimeFromTimeString($this->correctTime . ':00'); // Adiciona segundos

                // Determinar o tipo da nova batida (simplificado)
                // Se não houver batidas, é uma entrada. Se houver uma entrada, é uma saída, etc.
                // Esta lógica pode ser mais complexa dependendo das regras.
                // Para uma entrada manual, podemos deixar o tipo como 'manual_entry'
                // e o RH/gestor ajusta se necessário, ou o usuário especifica na justificativa.
                $newEntryType = TimeClockEntry::TYPE_MANUAL_ENTRY;

                TimeClockEntry::create([
                    'user_id' => $user->uuid,
                    'company_id' => $user->company_id,
                    'recorded_at' => $dateTimeToRecord,
                    'type' => $newEntryType,
                    'status' => TimeClockEntry::STATUS_JUSTIFIED, // Nasce justificada
                    'notes' => "REGISTRO MANUAL (Horário corrigido/adicionado pelo usuário em " . Carbon::now()->format('d/m/Y H:i') . "):\n" . $this->justificationNotes,
                    'ip_address' => request()->ip(), // Pode ser útil registrar o IP da justif.
                    'user_agent' => request()->userAgent(), // E o user agent
                ]);
            }

            // 2. Atualizar as batidas existentes do dia com a justificativa
            $entriesToUpdate = TimeClockEntry::where('user_id', $user->uuid)
                ->whereDate('recorded_at', $carbonDate)
                ->get();

            if (!$this->correctTime && $entriesToUpdate->isEmpty()) {
                // Se não informou horário correto E não há batidas, a justificativa é para a ausência total.
                // Poderíamos criar uma entrada de "Falta Justificada" ou apenas registrar a nota em algum lugar.
                // Por ora, se não há batidas e não foi fornecido um correctTime, apenas fechamos.
                // Ou, idealmente, o sistema deveria permitir registrar uma "Ausência Justificada".
                // Para este exemplo, vamos assumir que a justificativa sem correctTime e sem batidas
                // é uma nota para o dia, mas não cria uma batida.
                session()->flash('message', 'Justificativa para o dia salva. Nenhuma batida foi criada ou alterada pois não existem registros para este dia e nenhum horário específico foi informado.');
                $this->closeModalAndRefresh();
                return;
            }


            foreach ($entriesToUpdate as $entry) {
                // Adiciona a justificativa às notas existentes
                $entry->notes = ($entry->notes ? $entry->notes . "\n---\n" : '') . $justificationTextForExisting;

                // Mudar status para 'justified' se estava 'normal' ou 'alert'.
                if (in_array($entry->status, [TimeClockEntry::STATUS_NORMAL, TimeClockEntry::STATUS_ALERT])) {
                    $entry->status = TimeClockEntry::STATUS_JUSTIFIED;
                }
                $entry->save();
            }

            $this->closeModalAndRefresh();
        }
    }

    private function closeModalAndRefresh()
    {
        $this->closeModal();
        $this->dispatch('justificationSaved'); // Evento para a página principal
        // A mensagem de sucesso será mostrada na página principal após o reload
        if (!$this->correctTime && TimeClockEntry::where('user_id', Auth::id())->whereDate('recorded_at', Carbon::parse($this->selectedDate))->get()->isEmpty()){
            // Não mostra "salva com sucesso" se foi o caso de justificativa de ausência sem criação de ponto
        } else {
            session()->flash('message', 'Justificativa e/ou correção salva com sucesso!');
        }
    }


    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedDate = null;
        $this->justificationNotes = '';
        $this->correctTime = null;
        $this->entriesForDate = [];
        $this->modalTitle = '';
        $this->resetErrorBag(); // Limpa erros de validação anteriores
        $this->resetValidation(); // Limpa todos os erros de validação
    }

    public function render()
    {
        return view('livewire.time-clock.justification-modal');
    }
}
