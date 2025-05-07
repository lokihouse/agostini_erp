<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $modelLabel = 'Empresa'; // Tradução
    protected static ?string $pluralModelLabel = 'Empresas'; // Tradução

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Sistema';
    protected static ?string $navigationLabel = 'Empresas'; // Tradução para o menu
    protected static ?int $navigationSort = 2; // Ordem no menu (opcional, ajuste conforme necessário)


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nome Fantasia')
                    ->required()
                    ->maxLength(255),
                TextInput::make('socialName')
                    ->label('Razão Social')
                    ->maxLength(255),
                TextInput::make('taxNumber')
                    ->label('CNPJ')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->mask('99.999.999/9999-99')
                    ->dehydrateStateUsing(static fn (?string $state): ?string => $state ? preg_replace('/[^0-9]/', '', $state) : null)
                    ->rule(static function () {
                        return static function (string $attribute, $value, \Closure $fail) {
                            if (empty($value)) {
                                return;
                            }
                            $cleaned = preg_replace('/[^0-9]/', '', $value);
                            if (strlen($cleaned) !== 14) {
                                $fail('O campo CNPJ deve conter 14 dígitos.');
                            }
                        };
                    }),
                TextInput::make('address')
                    ->label('Endereço')
                    ->maxLength(255),
                TextInput::make('telephone')
                    ->label('Telefone')
                    ->tel()
                    ->dehydrateStateUsing(static fn (string $state): string => preg_replace('/[^0-9]/', '', $state))
                    ->mask('(99) 99999-9999')
                    ->dehydrateStateUsing(static fn (?string $state): ?string => $state ? preg_replace('/[^0-9]/', '', $state) : null)
                    ->rule(static function () {
                        return static function (string $attribute, $value, \Closure $fail) {
                            if (empty($value)) {
                                return;
                            }
                            $cleaned = preg_replace('/[^0-9]/', '', $value);
                            if (strlen($cleaned) !== 11) {
                                $fail('O campo Telefone deve conter 11 dígitos.');
                            }
                        };
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome Fantasia') // Tradução
                    ->searchable()
                    ->sortable(),
                TextColumn::make('socialName')
                    ->label('Razão Social')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('taxNumber')
                    ->label('CNPJ')
                    ->searchable()
                    ->formatStateUsing(function (?string $state): string {
                        if (empty($state)) {
                            return '';
                        }
                        $cleanedState = preg_replace('/[^0-9]/', '', $state);
                        if (strlen($cleanedState) === 14) {
                            return sprintf('%s.%s.%s/%s-%s',
                                substr($cleanedState, 0, 2),
                                substr($cleanedState, 2, 3),
                                substr($cleanedState, 5, 3),
                                substr($cleanedState, 8, 4),
                                substr($cleanedState, 12, 2)
                            );
                        }
                        return $state; // Retorna o estado original se não for um CNPJ válido
                    }),
                TextColumn::make('telephone')
                    ->label('Telefone')
                    ->searchable() // Adicionado searchable para telefone
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Criado em') // Tradução
                    ->dateTime('d/m/Y H:i') // Formato de data
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Atualizado em') // Tradução
                    ->dateTime('d/m/Y H:i') // Formato de data
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Excluído em') // Tradução
                    ->dateTime('d/m/Y H:i') // Formato de data
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->label('Mostrar Excluídos'), // Tradução
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Editar'),
                Tables\Actions\DeleteAction::make()->label('Excluir'),
                Tables\Actions\ForceDeleteAction::make()->label('Excluir Permanentemente'),
                Tables\Actions\RestoreAction::make()->label('Restaurar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Excluir Selecionados'), // Tradução
                    Tables\Actions\ForceDeleteBulkAction::make()->label('Excluir Perm. Selecionados'), // Tradução
                    Tables\Actions\RestoreBulkAction::make()->label('Restaurar Selecionados'), // Tradução
                ]),
            ])
            ->emptyStateHeading('Nenhuma empresa encontrada') // Tradução
            ->emptyStateDescription('Crie uma empresa para começar.'); // Tradução
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            // 'view' => Pages\ViewCompany::route('/{record}'), // Se você tiver uma página de visualização separada
            'edit' => Pages\EditCompany::route('/{record}/edit'),
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
