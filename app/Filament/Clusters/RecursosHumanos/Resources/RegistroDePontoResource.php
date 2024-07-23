<?php

namespace App\Filament\Clusters\RecursosHumanos\Resources;

use App\Filament\Clusters\RecursosHumanos;
use App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource\Pages;
use App\Filament\Clusters\RecursosHumanos\Resources\RegistroDePontoResource\RelationManagers;
use App\Models\RegistroDePonto;
use Carbon\Carbon;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;

class RegistroDePontoResource extends Resource
{
    protected static ?string $model = RegistroDePonto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = RecursosHumanos::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('dia', 'desc')
            ->columns([
                TextColumn::make('funcionario.name')
                    ->label('Funcionário')
                    ->searchable(),
                TextColumn::make('dia')
                    ->date('j \d\e F \d\e Y')
                    ->extraHeaderAttributes(['class' => 'w-1']),
                TextColumn::make('hora')
                    ->date('H:i')
                    ->extraHeaderAttributes(['class' => 'w-1']),
                Tables\Columns\IconColumn::make('status')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->icon(fn(string $state): string => match ($state) {
                        'valido' => 'heroicon-o-arrow-down-circle',
                        'em analise' => 'heroicon-o-exclamation-triangle',
                        'concluido' => 'heroicon-o-check-circle',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'valido' => 'success',
                        'em analise' => 'danger',
                        'concluido' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('justificado')
                    ->extraHeaderAttributes(['class' => 'w-1'])
                    ->boolean()
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Usuário')
                    ->searchable()
                    ->relationship('funcionario', 'name'),
                DateRangeFilter::make('dia')
                    ->maxDate(Carbon::now())
                    ->withIndicator(),
                SelectFilter::make('status')
                    ->multiple()
                    ->default(['valido', 'em analise'])
                    ->options([
                        'valido' => 'Válido',
                        'em analise' => 'Em Análise',
                        'concluido' => 'Concluído'
                    ])
            ])
            ->bulkActions([
                BulkAction::make('validar registros')
                    ->button()
                    //->hidden(!Auth::user()->hasPermissionTo('update RegistroDePonto'))
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->status = 'concluido';
                            $record->save();
                        }
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistroDePontos::route('/'),
            'create' => Pages\CreateRegistroDePonto::route('/create'),
            'edit' => Pages\EditRegistroDePonto::route('/{record}/edit'),
        ];
    }
}
