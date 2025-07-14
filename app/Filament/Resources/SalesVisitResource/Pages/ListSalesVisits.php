<?php

namespace App\Filament\Resources\SalesVisitResource\Pages;

use App\Filament\Resources\SalesVisitResource;
use App\Models\SalesVisit;
use Filament\Actions;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;

class ListSalesVisits extends ListRecords
{
    protected static string $resource = SalesVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            Actions\Action::make('create')
                ->modalWidth(MaxWidth::ExtraSmall)
                ->form([
                    Select::make('client_id')
                        ->label('Cliente')
                        ->relationship('client', 'name', modifyQueryUsing: fn (Builder $query) => $query->orderBy('name'))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->columnSpanFull(),

                    Select::make('assigned_to_user_id')
                        ->label('Vendedor Responsável')
                        ->relationship('assignedTo', 'name', modifyQueryUsing: function (Builder $query) {
                            return $query
                                ->where('company_id', auth()->user()->company_id)
                                ->where('is_active', true)
                                ->orderBy('name');
                        })
                        ->searchable()
                        ->preload()
                        ->required(),

                    DateTimePicker::make('scheduled_at')
                        ->label('Data/Hora Agendada')
                        ->native(false)
                        ->seconds(false)
                        ->required()
                        ->default(now()->addDay()->setHour(9)->setMinute(0)),
                ])
                ->label('Agendar Nova Visita')
                ->action(function (array $data): void {
                    try {
                        SalesVisit::create($data);

                        Notification::make()
                            ->title('Visita Agendada')
                            ->body('A nova visita de venda foi agendada com sucesso.')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro ao Agendar Visita')
                            ->body('Ocorreu um erro ao tentar agendar a visita: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
