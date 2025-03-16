<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JornadaDeTrabalhoResource\Pages;
use App\Filament\Resources\JornadaDeTrabalhoResource\RelationManagers;
use App\Models\JornadaDeTrabalho;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Form;

use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Njxqlus\Filament\Components\Forms\RelationManager;

class JornadaDeTrabalhoResource extends ResourceBase
{
    protected static ?string $model = JornadaDeTrabalho::class;
    protected static ?string $navigationGroup = 'R.H.';
    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static ?int $navigationSort = 51;


    public static function form(Form $form): Form
    {
        return $form
            ->columns(20)
            ->schema([
                Group::make([
                    TextInput::make('nome')->required(),
                    Textarea::make('descricao'),
                    TextInput::make('dias_de_ciclo')
                        ->required()
                        ->numeric(),
                    TextInput::make('carga_horaria_acumulada')
                        ->visibleOn('edit')
                        ->suffix('hh:mm')
                        ->readOnly()
                ])->columnSpan(5),
                Forms\Components\Tabs::make()
                    ->visibleOn('edit')
                    ->schema([
                        Forms\Components\Tabs\Tab::make('Horarios de Trabalho')
                            ->columns(2)
                            ->schema([
                                ViewField::make('agenda')
                                    ->view('filament.forms.jornada_de_trabalho.agenda'),
                                Group::make([
                                    RelationManager::make()
                                        ->manager(
                                            RelationManagers\HorariosDeTrabalhoRelationManager::class
                                        )
                                        ->lazy(true),
                                    Forms\Components\Placeholder::make('info_horario_de_trabalho')
                                        ->label('')
                                        ->content('* Linhas coloridas de vermelho não são contabilizadas. Verifique os dias de ciclo.')
                                ])->columnSpan(1),
                            ]),
                        Forms\Components\Tabs\Tab::make('Usuários')->schema([
                            RelationManager::make()
                                ->manager(
                                    RelationManagers\UsuariosRelationManager::class
                                )
                                ->lazy(true),
                        ]),
                    ])
                    ->columnSpan(15),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome'),
                TextColumn::make('dias_de_ciclo')
                    ->alignCenter()
                    ->width(1),
                TextColumn::make('carga_horaria_acumulada')
                    ->alignCenter()
                    ->width(1),
            ])
            ->filters([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJornadaDeTrabalhos::route('/'),
            'create' => Pages\CreateJornadaDeTrabalho::route('/create'),
            'edit' => Pages\EditJornadaDeTrabalho::route('/{record}/edit'),
        ];
    }
}
