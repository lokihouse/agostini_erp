<?php

namespace App\Filament\Resources\WorkShiftResource\Pages;

use App\Filament\Resources\WorkShiftResource;
use App\Models\WorkShiftDay; // Certifique-se que está importado
use App\Utils\WorkShiftCalculator; // Se você moveu as funções para cá
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;

class EditWorkShift extends EditRecord
{
    protected static string $resource = WorkShiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Prepara os dados do formulário antes de preenchê-lo na edição.
     * Crucial para popular o Repeater da jornada semanal.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // $data já contém os atributos do modelo WorkShift principal (incluindo 'type')
        $workShift = $this->getRecord(); // Pega o registro WorkShift atual

        if ($workShift->type === 'weekly') {
            $existingDays = $workShift->workShiftDays()
                ->get()
                ->keyBy('day_of_week'); // Chaveia pela coluna day_of_week para fácil acesso

            $daysStructure = [];
            // Ordem desejada no formulário: Domingo (7) a Sábado (6)
            $dayOrder = [7, 1, 2, 3, 4, 5, 6]; // Sun, Mon, Tue, Wed, Thu, Fri, Sat

            foreach ($dayOrder as $dayOfWeek) {
                $existingDay = $existingDays->get($dayOfWeek);
                if ($existingDay) {
                    $daysStructure[] = [
                        'day_of_week'        => (int) $existingDay->day_of_week,
                        'is_off_day'         => (bool) $existingDay->is_off_day,
                        'starts_at'          => $existingDay->starts_at,
                        'ends_at'            => $existingDay->ends_at,
                        'interval_starts_at' => $existingDay->interval_starts_at,
                        'interval_ends_at'   => $existingDay->interval_ends_at,
                    ];
                } else {
                    // Dia não existe no banco, cria um placeholder default para o formulário
                    $daysStructure[] = [
                        'day_of_week'        => (int) $dayOfWeek,
                        'is_off_day'         => true, // Default para folga se não configurado
                        'starts_at'          => null,
                        'ends_at'            => null,
                        'interval_starts_at' => null,
                        'interval_ends_at'   => null,
                    ];
                }
            }
            $data['workShiftDays_form_data'] = $daysStructure;
        } else {
            // Se o tipo não for semanal, garante que o array esteja vazio
            // para não causar problemas se o usuário mudar o tipo para semanal.
            $data['workShiftDays_form_data'] = [];
        }

        // Para depuração, você pode descomentar a linha abaixo:
        // dump('EditWorkShift - mutateFormDataBeforeFill - Final Data:', $data);

        return $data;
    }

    /**
     * Lida com a atualização do registro e seus dias associados.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // $record é a instância de WorkShift
        $record->update($data); // Atualiza os campos do WorkShift principal

        if ($data['type'] === 'weekly' && isset($data['workShiftDays_form_data'])) {
            // Sincroniza os dias da semana
            foreach ($data['workShiftDays_form_data'] as $dayData) {
                if (isset($dayData['day_of_week'])) {
                    $record->workShiftDays()->updateOrCreate(
                        [
                            // Condições para encontrar o registro existente
                            'day_of_week' => $dayData['day_of_week']
                        ],
                        [
                            // Valores para atualizar ou criar
                            'is_off_day'         => $dayData['is_off_day'] ?? false,
                            'starts_at'          => ($dayData['is_off_day'] ?? false) ? null : ($dayData['starts_at'] ?? null),
                            'ends_at'            => ($dayData['is_off_day'] ?? false) ? null : ($dayData['ends_at'] ?? null),
                            'interval_starts_at' => ($dayData['is_off_day'] ?? false) ? null : ($dayData['interval_starts_at'] ?? null),
                            'interval_ends_at'   => ($dayData['is_off_day'] ?? false) ? null : ($dayData['interval_ends_at'] ?? null),
                        ]
                    );
                }
            }
        } elseif ($data['type'] === 'cyclical') {
            // Se mudou de semanal para cíclico, remove os dias semanais antigos.
            $record->workShiftDays()->delete();
        }

        return $record;
    }

    /**
     * Validação customizada após a validação padrão do Filament.
     */
    protected function afterValidate(): void
    {
        $data = $this->data; // Acessa os dados validados
        $customErrors = [];

        // Início da sua lógica de validação customizada
        // Exemplo:
        // if (isset($data['type']) && $data['type'] === 'weekly') {
        //     $workShiftDays = $data['workShiftDays_form_data'] ?? [];
        //     if (empty($workShiftDays) || count($workShiftDays) !== 7) {
        //         $customErrors['workShiftDays_form_data'] = 'É necessário definir os 7 dias para a jornada semanal.';
        //     }
        //     // ... mais validações ...
        // }

        // Se houver erros customizados, lança a exceção
        if (!empty($customErrors)) {
            throw ValidationException::withMessages($customErrors);
        }
    }
}
