<?php

namespace App\Filament\Resources\WorkShiftResource\Pages;

use App\Filament\Resources\WorkShiftResource;
use App\Models\WorkShiftDay; // Certifique-se que está importado
use App\Utils\WorkShiftCalculator; // Certifique-se que o namespace está correto
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model; // Importar Model para o tipo de retorno de handleRecordCreation

class CreateWorkShift extends CreateRecord
{
    protected static string $resource = WorkShiftResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Método para inicializar os dados do formulário,
     * especialmente para a jornada semanal.
     */
    public function mount(): void
    {
        parent::mount(); // Chama o mount da classe pai

        // Define um valor inicial para 'type' se não estiver definido,
        // para que a aba correta e os dados possam ser pré-carregados.
        // Se 'type' já tiver um valor (ex: vindo de query string), respeita-o.
        $initialType = $this->data['type'] ?? 'weekly'; // Default para 'weekly'
        $this->form->fill(['type' => $initialType]); // Preenche o campo 'type' no formulário

        if ($initialType === 'weekly' && empty($this->data['workShiftDays_form_data'])) {
            $this->data['workShiftDays_form_data'] = $this->getDefaultWeeklyDaysStructure();
            // Preenche o repeater no formulário
            $this->form->fill(['workShiftDays_form_data' => $this->data['workShiftDays_form_data']]);
        }
    }

    /**
     * Retorna a estrutura padrão para os 7 dias da semana.
     */
    protected function getDefaultWeeklyDaysStructure(): array
    {
        $daysStructure = [];
        // Ordem: Domingo (7) a Sábado (6) para consistência com a exibição no Resource
        $dayOrder = [7, 1, 2, 3, 4, 5, 6]; // Sun, Mon, Tue, Wed, Thu, Fri, Sat

        foreach ($dayOrder as $dayOfWeek) {
            $daysStructure[] = [
                'day_of_week'        => $dayOfWeek,
                'is_off_day'         => in_array($dayOfWeek, [7, 6]), // Ex: Domingo e Sábado como folga por padrão
                'starts_at'          => null,
                'ends_at'            => null,
                'interval_starts_at' => null,
                'interval_ends_at'   => null,
            ];
        }
        return $daysStructure;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();
        if ($user && $user->company_id) {
            $data['company_id'] = $user->company_id;
        } else {
            // Se o usuário não tem company_id e não é super_admin (ou se super_admin também precisa de company_id)
            // Adapte conforme sua regra para super_admin
            if (!$user || !$user->hasRole(config('filament-shield.super_admin.name'))) {
                throw ValidationException::withMessages([
                    'company_id' => 'Não foi possível associar a jornada a uma empresa. Usuário sem empresa definida.',
                ]);
            }
            // Se super_admin pode criar para qualquer empresa, ele precisaria de um campo para selecionar a empresa.
            // Se super_admin também é escopado pela sua própria company_id, a lógica acima já cobre.
        }
        return $data;
    }

    /**
     * Lida com a criação do registro WorkShift e seus WorkShiftDay associados.
     */
    protected function handleRecordCreation(array $data): Model // Especifica o tipo de retorno
    {
        // Cria o registro WorkShift principal
        $workShift = static::getModel()::create($data);

        // Se for do tipo semanal, cria os WorkShiftDay
        if ($data['type'] === 'weekly' && isset($data['workShiftDays_form_data'])) {
            foreach ($data['workShiftDays_form_data'] as $dayData) {
                if (isset($dayData['day_of_week'])) { // Garante que o dia da semana está presente
                    $workShift->workShiftDays()->create([
                        'day_of_week'        => $dayData['day_of_week'],
                        'is_off_day'         => $dayData['is_off_day'] ?? false,
                        'starts_at'          => ($dayData['is_off_day'] ?? false) ? null : ($dayData['starts_at'] ?? null),
                        'ends_at'            => ($dayData['is_off_day'] ?? false) ? null : ($dayData['ends_at'] ?? null),
                        'interval_starts_at' => ($dayData['is_off_day'] ?? false) ? null : ($dayData['interval_starts_at'] ?? null),
                        'interval_ends_at'   => ($dayData['is_off_day'] ?? false) ? null : ($dayData['interval_ends_at'] ?? null),
                    ]);
                }
            }
        }
        return $workShift;
    }
}
