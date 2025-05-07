<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkShiftResource\Pages;
use App\Models\WorkShift;
use App\Utils\WorkShiftCalculator; // Certifique-se que o namespace está correto
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Auth;

class WorkShiftResource extends Resource
{
    protected static ?string $model = WorkShift::class;

    protected static ?string $modelLabel = 'Jornada de Trabalho';
    protected static ?string $pluralModelLabel = 'Jornadas de Trabalho';

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'R.H.';
    protected static ?int $navigationSort = 51;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('WorkShiftTabs')
                    ->tabs([
                        Tabs\Tab::make('Informações Gerais')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome da Jornada')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('type')
                                    ->label('Tipo de Jornada')
                                    ->options([
                                        'weekly' => 'Semanal',
                                        'cyclical' => 'Cíclica',
                                    ])
                                    ->required()
                                    ->live() // Essencial para a reatividade das abas e do cálculo
                                    ->afterStateUpdated(function (callable $set, Get $get, $state) {
                                        if ($state === 'weekly') {
                                            $set('cycle_work_duration_hours', null);
                                            $set('cycle_off_duration_hours', null);
                                            $set('cycle_shift_starts_at', null);
                                            $set('cycle_shift_ends_at', null);
                                            $set('cycle_interval_starts_at', null);
                                            $set('cycle_interval_ends_at', null);

                                            // Se workShiftDays_form_data estiver vazio, inicializa (a Page Class também faz isso no load)
                                            if (empty($get('workShiftDays_form_data'))) {
                                                // A inicialização principal ocorre nas Page Classes (Create/Edit)
                                                // mas podemos garantir uma estrutura base aqui se necessário ao mudar o tipo
                                                $defaultDays = [];
                                                $dayOrder = [7, 1, 2, 3, 4, 5, 6];
                                                foreach ($dayOrder as $dayOfWeek) {
                                                    $defaultDays[] = [
                                                        'day_of_week' => $dayOfWeek,
                                                        'is_off_day' => in_array($dayOfWeek, [7, 6]), // Dom e Sab como folga por default
                                                        'starts_at' => null, 'ends_at' => null,
                                                        'interval_starts_at' => null, 'interval_ends_at' => null,
                                                    ];
                                                }
                                                $set('workShiftDays_form_data', $defaultDays);
                                            }

                                        } elseif ($state === 'cyclical') {
                                            $set('workShiftDays_form_data', []);
                                        }
                                    }),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Observações')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tabs\Tab::make('Detalhes da Jornada Semanal')
                            ->icon('heroicon-o-calendar-days')
                            ->visible(fn(Get $get): bool => $get('type') === 'weekly')
                            ->label(function (Get $get): string { // <-- LABEL DINÂMICO AQUI
                                $baseLabel = 'Jornada Semanal'; // Alterado para ser mais curto
                                $workShiftDays = $get('workShiftDays_form_data');
                                $totalNetWorkMinutes = 0;

                                if (is_array($workShiftDays)) {
                                    foreach ($workShiftDays as $dayData) {
                                        if (empty($dayData['is_off_day'])) {
                                            $s = $dayData['starts_at'] ?? null;
                                            $e = $dayData['ends_at'] ?? null;
                                            $is = $dayData['interval_starts_at'] ?? null;
                                            $ie = $dayData['interval_ends_at'] ?? null;

                                            // Só calcula se tiver início e fim, para evitar erros com dados incompletos
                                            if ($s && $e) {
                                                $totalNetWorkMinutes += WorkShiftCalculator::calculateNetWorkDuration($s, $e, $is, $ie);
                                            }
                                        }
                                    }
                                }

                                if ($totalNetWorkMinutes > 0) {
                                    $hours = floor($totalNetWorkMinutes / 60);
                                    $minutes = $totalNetWorkMinutes % 60;
                                    return $baseLabel . ' - ' . $hours . 'h' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . 'min';
                                }
                                return $baseLabel;
                            })
                            ->schema([
                                // Cabeçalhos da "tabela"
                                Grid::make(12)
                                    ->schema([
                                        Forms\Components\Placeholder::make('header_day_name')
                                            ->label(false)->content('Dia')->columnSpan(2),
                                        Forms\Components\Placeholder::make('header_starts_at')
                                            ->label(false)->content('Entrada')->columnSpan(2),
                                        Forms\Components\Placeholder::make('header_interval_starts_at')
                                            ->label(false)->content('In. Intervalo')->columnSpan(2),
                                        Forms\Components\Placeholder::make('header_interval_ends_at')
                                            ->label(false)->content('Fim Intervalo')->columnSpan(2),
                                        Forms\Components\Placeholder::make('header_ends_at')
                                            ->label(false)->content('Saída')->columnSpan(2),
                                        Forms\Components\Placeholder::make('header_is_off_day')
                                            ->label(false)->content('Folga')->columnSpan(2),
                                    ])
                                    ->columnSpanFull()
                                    ->extraAttributes(['class' => 'mb-2 font-semibold text-sm text-gray-700 dark:text-gray-300']),

                                Repeater::make('workShiftDays_form_data')
                                    ->label(false)
                                    ->schema([
                                        Forms\Components\Hidden::make('day_of_week'),
                                        Forms\Components\Placeholder::make('day_name_display')
                                            ->label(false)
                                            ->content(function (Get $get): string {
                                                $dayNum = $get('day_of_week');
                                                if (!$dayNum) return '';
                                                $days = [7 => 'Domingo', 1 => 'Segunda', 2 => 'Terça', 3 => 'Quarta', 4 => 'Quinta', 5 => 'Sexta', 6 => 'Sábado'];
                                                return $days[$dayNum] ?? 'Dia Inválido';
                                            })
                                            ->columnSpan(fn(Get $get): int => $get('is_off_day') ? 10 : 2),

                                        TimePicker::make('starts_at')
                                            ->label(false)->placeholder('HH:MM')->seconds(false)
                                            ->hidden(fn(Get $get) => $get('is_off_day') === true)
                                            ->required(fn(Get $get) => $get('is_off_day') === false)
                                            ->live(onBlur: true) // Atualiza ao perder o foco para performance
                                            ->columnSpan(2),

                                        TimePicker::make('interval_starts_at')
                                            ->label(false)->placeholder('HH:MM')->seconds(false)
                                            ->hidden(fn(Get $get) => $get('is_off_day') === true)
                                            ->live(onBlur: true)
                                            ->columnSpan(2),

                                        TimePicker::make('interval_ends_at')
                                            ->label(false)->placeholder('HH:MM')->seconds(false)
                                            ->hidden(fn(Get $get) => $get('is_off_day') === true)
                                            ->live(onBlur: true)
                                            ->columnSpan(2),

                                        TimePicker::make('ends_at')
                                            ->label(false)->placeholder('HH:MM')->seconds(false)
                                            ->hidden(fn(Get $get) => $get('is_off_day') === true)
                                            ->required(fn(Get $get) => $get('is_off_day') === false)
                                            ->live(onBlur: true)
                                            ->columnSpan(2),

                                        Toggle::make('is_off_day')
                                            ->label(false)
                                            ->live() // Essencial para a reatividade do layout e do cálculo
                                            ->default(false)
                                            ->columnSpan(2)
                                            ->inline(false),
                                    ])
                                    ->columns(12)
                                    ->minItems(7)->maxItems(7)
                                    ->deletable(false)->reorderable(false)->addable(false)
                                    ->columnSpanFull(),
                            ]),

                        Tabs\Tab::make('Detalhes da Jornada Cíclica')
                            ->icon('heroicon-o-arrow-path')
                            ->visible(fn(Get $get): bool => $get('type') === 'cyclical')
                            ->schema([
                                Forms\Components\TextInput::make('cycle_work_duration_hours')
                                    ->label('Duração do Turno no Ciclo (horas)')
                                    ->numeric()->minValue(1)
                                    ->helperText('Ex: 12 para uma escala 12x36.'),
                                Forms\Components\TextInput::make('cycle_off_duration_hours')
                                    ->label('Duração da Folga no Ciclo (horas)')
                                    ->numeric()->minValue(1)
                                    ->helperText('Ex: 36 para uma escala 12x36.'),
                                TimePicker::make('cycle_shift_starts_at')
                                    ->label('Início do Turno no Ciclo')->seconds(false),
                                TimePicker::make('cycle_shift_ends_at')
                                    ->label('Fim do Turno no Ciclo')->seconds(false),
                                TimePicker::make('cycle_interval_starts_at')
                                    ->label('Início do Intervalo no Ciclo')->seconds(false),
                                TimePicker::make('cycle_interval_ends_at')
                                    ->label('Fim do Intervalo no Ciclo')->seconds(false),
                            ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nome da Jornada')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'weekly' => 'Semanal',
                        'cyclical' => 'Cíclica',
                        default => $state,
                    })
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'weekly' => 'Semanal',
                        'cyclical' => 'Cíclica',
                    ])
                    ->label('Tipo de Jornada'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkShifts::route('/'),
            'create' => Pages\CreateWorkShift::route('/create'),
            'edit' => Pages\EditWorkShift::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && $user->company_id) {
            return $query->where('company_id', $user->company_id);
        }
        return $query->whereRaw('1 = 0');
    }
}
