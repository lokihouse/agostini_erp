<?php

namespace App\Filament\Resources\TimeClockEntryResource\Pages;

use App\Filament\Resources\TimeClockEntryResource;
use App\Models\TimeClockEntry;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;

class ListTimeClockEntries extends ListRecords
{
    protected static string $resource = TimeClockEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            $this->getManualCreateAction(),
        ];
    }

    protected function getManualCreateAction(): Actions\Action
    {
        return Actions\Action::make('createManualEntry')
            ->modalWidth(MaxWidth::ExtraSmall)
            ->label('Lançar Batida Manual')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->form([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Funcionário')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live(onBlur: true), // live() para buscar a empresa do usuário
                DateTimePicker::make('recorded_at')
                    ->label('Data e Hora da Batida')
                    ->required()
                    ->seconds(false) // Geralmente não se insere segundos manualmente
                    ->default(now()),
            ])
            ->action(function (array $data): void {
                $loggedInUser = Auth::user();
                $loggedInUserName = $loggedInUser ? $loggedInUser->name : 'Sistema';

                $selectedUser = User::find($data['user_id']);
                $companyId = null;

                if ($selectedUser) {
                    $companyId = $selectedUser->company_id;
                } else {
                    // Se o usuário selecionado não for encontrado, não podemos prosseguir
                    Notification::make()
                        ->title('Erro ao lançar batida')
                        ->body('Funcionário selecionado não encontrado.')
                        ->danger()
                        ->send();
                    return;
                }

                if (!$companyId) {
                    // Se o funcionário selecionado não tiver uma empresa, e company_id for obrigatório
                    Notification::make()
                        ->title('Erro ao lançar batida')
                        ->body('O funcionário selecionado não possui uma empresa associada.')
                        ->danger()
                        ->send();
                    return;
                }

                TimeClockEntry::create([
                    'user_id' => $data['user_id'],
                    'recorded_at' => $data['recorded_at'],
                    'company_id' => $companyId,
                    'type' => TimeClockEntry::TYPE_MANUAL_ENTRY, // Use a constante do modelo
                    'status' => TimeClockEntry::STATUS_APPROVED, // Use a constante do modelo
                    'notes' => 'Batida lançada por [' . $loggedInUserName . '] manualmente',
                    'approved_by' => $loggedInUser->uuid,
                    'approved_at' => Carbon::now()
                ]);

                Notification::make()
                    ->title('Batida manual lançada com sucesso!')
                    ->success()
                    ->send();
            });
    }
}
