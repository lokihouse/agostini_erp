<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $modelLabel = 'Veículo';
    protected static ?string $pluralModelLabel = 'Veículos';
    protected static ?string $navigationGroup = 'Cargas'; // Novo grupo
    protected static ?int $navigationSort = 70; // Para ser o último (ajuste conforme necessário)

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('license_plate')
                    ->label('Placa')
                    ->required()
                    ->maxLength(10) // Ex: ABC-1234 ou ABC1D23
                    ->unique(ignoreRecord: true, modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, callable $get) {
                        return $rule->where('company_id', $get('../../company_id') ?? auth()->user()->company_id);
                    }),
                Forms\Components\TextInput::make('description')
                    ->label('Descrição (Ex: HR Azul, Moto CG Vermelha)')
                    ->maxLength(255),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('brand')
                        ->label('Marca')
                        ->maxLength(100),
                    Forms\Components\TextInput::make('model_name')
                        ->label('Modelo')
                        ->maxLength(100),
                ]),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('year_manufacture')
                        ->label('Ano Fabricação')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(fn() => date('Y') + 1),
                    Forms\Components\TextInput::make('year_model')
                        ->label('Ano Modelo')
                        ->numeric()
                        ->minValue(1900)
                        ->maxValue(fn() => date('Y') + 2),
                ]),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('color')
                        ->label('Cor')
                        ->maxLength(50),
                    Forms\Components\TextInput::make('renavam')
                        ->label('RENAVAM')
                        ->maxLength(11)
                        ->numeric(),
                ]),
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('cargo_volume_m3')
                        ->label('Volume Carga (m³)')
                        ->numeric()
                        ->inputMode('decimal')
                        ->step('0.001'),
                    Forms\Components\TextInput::make('max_load_kg')
                        ->label('Carga Máxima (KG)')
                        ->numeric()
                        ->inputMode('decimal')
                        ->step('0.01'),
                ]),
                Forms\Components\Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true)
                    ->required(),
                Forms\Components\Textarea::make('notes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('license_plate')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrição')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand')
                    ->label('Marca')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('model_name')
                    ->label('Modelo')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
