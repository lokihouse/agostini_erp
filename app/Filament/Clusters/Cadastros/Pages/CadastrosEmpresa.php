<?php

namespace App\Filament\Clusters\Cadastros\Pages;

use App\Filament\Actions\Form\EmpresaLocalizarCep;
use App\Filament\Actions\Form\EmpresaLocalizarCnpj;
use App\Filament\Clusters\Cadastros;
use App\Forms\Components\EmpresaRecursosHumanosMapa;
use App\Models\Empresa;
use App\Utils\DateHelper;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Pages\Page;
use Filament\Support\Enums\VerticalAlignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class CadastrosEmpresa extends Page implements HasForms
{
    use HasPageShield;
    use InteractsWithForms;

    protected ?string $heading = 'Empresa';
    protected static ?string $title = 'Cadastros - Home';
    protected static ?string $navigationLabel = 'Empresa';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static string $view = 'filament.clusters.cadastros.pages.cadastros-empresa';
    protected static ?string $cluster = Cadastros::class;

    protected static ?int $navigationSort = 1;



    public ?array $data = [];

    public function mount(): void
    {
        $this->data = Empresa::query()->where('id', Auth::user()->empresa_id)->first()->toArray();
        // $this->data['horarios'] = json_decode($this->data['horarios'], true);
    }

    protected function getHeaderActions(): array
    {
        return [
            /*Actions\Action::make('update')
                ->label('Atualizar')
                ->hidden(fn () => !Auth::user()->can('update_empresa'))
                ->action(function($data, $record) {
                    dd($data, $record);
                })*/
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema(components: [
                Section::make('Dados da Empresa')
                    ->compact()
                    ->collapsible()
                    ->schema([
                        Group::make([
                            Placeholder::make('cnpj')
                                ->label('CNPJ')
                                ->columnSpan([ 'default' => 1, 'md' => 2 ])
                                ->content(fn ($state) => $state),
                            Placeholder::make('razao_social')
                                ->label('Razão Social')
                                ->columnSpan([ 'default' => 1, 'md' => 3 ])
                                ->content(fn ($state) => $state),
                            TextInput::make('nome_fantasia')
                                ->label('Nome Fantasia')
                                ->columnSpan([ 'default' => 1, 'md' => 3 ])
                                ->required(),
                            TextInput::make('email')
                                ->label('Email')
                                ->placeholder('contato@empresa.com.br')
                                ->columnSpan([ 'default' => 1, 'md' => 2 ]),
                            TextInput::make('telefone')
                                ->label('Telefone')
                                ->placeholder('(00) 00000-0000')
                                ->mask('(99) 99999-9999')
                                ->columnSpan([ 'default' => 1, 'md' => 2 ]),
                            TextInput::make('cep')
                                ->label('CEP')
                                ->columnSpan([ 'default' => 4, 'md' => 2 ])
                                ->placeholder('00000-000')
                                ->mask('99999-999')
                                ->required(),
                            TextInput::make('logradouro')
                                ->label('Logradouro')
                                ->columnSpan([ 'default' => 4, 'md' => 3 ])
                                ->required(),
                            TextInput::make('numero')
                                ->label('Número')
                                ->columnSpan([ 'default' => 2, 'md' => 1 ])
                                ->required(),
                            TextInput::make('complemento')
                                ->label('Compl.')
                                ->columnSpan([ 'default' => 2, 'md' => 1 ]),
                            TextInput::make('bairro')
                                ->label('Bairro')
                                ->columnSpan([ 'default' => 1, 'md' => 2 ])
                                ->required(),
                            TextInput::make('municipio')
                                ->label('Município')
                                ->columnSpan([ 'default' => 1, 'md' => 2 ])
                                ->required(),
                            Select::make('uf')
                                ->label('UF')
                                ->columnSpan([ 'default' => 1, 'md' => 1 ])
                                ->options([
                                    "AC" => "AC",
                                    "AL" => "AL",
                                    "AP" => "AP",
                                    "AM" => "AM",
                                    "BA" => "BA",
                                    "CE" => "CE",
                                    "DF" => "DF",
                                    "ES" => "ES",
                                    "GO" => "GO",
                                    "MA" => "MA",
                                    "MT" => "MT",
                                    "MS" => "MS",
                                    "MG" => "MG",
                                    "PA" => "PA",
                                    "PB" => "PB",
                                    "PR" => "PR",
                                    "PE" => "PE",
                                    "PI" => "PI",
                                    "RJ" => "RJ",
                                    "RN" => "RN",
                                    "RS" => "RS",
                                    "RO" => "RO",
                                    "RR" => "RR",
                                    "SC" => "SC",
                                    "SP" => "SP",
                                    "SE" => "SE",
                                    "TO" => "TO",
                                ])
                                ->required()
                        ])
                            ->columns([ 'default' => 1, 'md' => 12 ]),
                    ]),
                /*Section::make('Recursos Humanos')
                    ->compact()
                    ->collapsible()
                    ->columns([ 'default' => 1, 'md' => 12 ])
                    ->schema([
                        Group::make([
                            EmpresaRecursosHumanosMapa::make('cerca_geografica_mapa')
                                ->label('Cerca Geográfica')
                                ->columnSpanFull(),
                            TextInput::make('raio_cerca')
                                ->label('Raio da Cerca')
                                ->suffix('m')
                                ->numeric()
                                ->columnSpan(1),
                            \Filament\Forms\Components\Actions::make([
                                Action::make('update')
                                    ->label('Atualizar')
                                    ->action(function(Set $set, $state){
                                        $set('cerca_geografica_mapa', [
                                            'latitude' => $state['latitude'],
                                            'longitude' => $state['longitude'],
                                            'raio' => $state['raio_cerca']
                                        ]);
                                    })
                            ])
                                ->verticalAlignment(VerticalAlignment::End)
                                ->fullWidth()
                        ])
                            ->columns([ 'default' => 1, 'md' => 2 ])
                            ->columnSpan([ 'default' => 1, 'md' => 3 ])
                    ]),*/
            ])
            //->disabled(fn() => !Auth::user()->can('update_empresa'))
            ->disabled()
            ->statePath('data');
    }
}
