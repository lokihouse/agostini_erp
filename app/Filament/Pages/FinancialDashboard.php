<?php

namespace App\Filament\Pages;

use App\Models\ChartOfAccount;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FinancialDashboard extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    public array $data = []; // Property to hold form state

    public Collection $reportData;
    public array $monthHeaders = [];

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 51; // Mantido o seu sort
    protected static ?string $title = 'Resumo Financeiro';
    protected static string $view = 'filament.pages.financial-dashboard';

    /**
     * Tells the form to store its state in the $this->data property.
     */
    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function mount(): void
    {
        // Fill the form, which will populate $this->data due to getFormStatePath()
        $this->form->fill([
            'startDate' => Carbon::now()->startOfMonth()->subMonths(11)->format('Y-m-d'),
            'endDate' => Carbon::now()->endOfMonth()->format('Y-m-d'),
        ]);

        $this->updateReportData();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate') // Will map to $this->data['startDate']
            ->label('Data Inicial')
                ->default(Carbon::now()->startOfMonth()->subMonths(11))
                ->native(false)
                ->reactive()
                ->required()
                ->afterStateUpdated(fn (FinancialDashboard $livewire) => $livewire->updateReportData()),

            DatePicker::make('endDate') // Will map to $this->data['endDate']
            ->label('Data Final')
                ->default(Carbon::now()->endOfMonth())
                ->native(false)
                ->reactive()
                ->minDate(fn (callable $get) => $get('startDate')) // $get se refere a $this->data
                ->required()
                ->afterStateUpdated(fn (FinancialDashboard $livewire) => $livewire->updateReportData()),
        ];
    }

    protected function updateReportData(): void
    {
        try {
            // 1. Executa a validação. Se falhar, uma ValidationException será lançada.
            // O propósito principal aqui é garantir que as regras sejam verificadas e os erros exibidos.
            $this->form->validate();
        } catch (ValidationException $e) {
            Log::warning('FinancialDashboard: Validation failed.', ['errors' => $e->errors(), 'form_data' => $this->data]);
            $this->reportData = collect();
            $this->monthHeaders = [];
            // A notificação de erro de validação geralmente é tratada automaticamente pelo Filament.
            return;
        }

        // 2. Se a validação passou (nenhuma exceção), $this->data deve conter o estado válido.
        // Acesse $this->data diretamente.
        $currentFormData = $this->data;

        // 3. Verificação defensiva adicional em $this->data.
        // Adicionada verificação de empty() porque ->required() deve garantir que não sejam nulos,
        // mas uma string vazia passaria no isset(). Carbon::parse('') falharia.
        if (
            !isset($currentFormData['startDate']) || empty($currentFormData['startDate']) ||
            !isset($currentFormData['endDate']) || empty($currentFormData['endDate'])
        ) {
            Log::error('FinancialDashboard: CRITICAL - Form state ($this->data) is missing required date keys or they are empty, after validation supposedly passed.', [
                'current_form_data_property' => $currentFormData,
            ]);

            $this->reportData = collect();
            $this->monthHeaders = [];
            Notification::make()
                ->danger()
                ->title('Erro Interno do Filtro')
                ->body('Não foi possível processar as datas do filtro. Por favor, tente novamente ou contate o suporte.')
                ->send();
            return;
        }

        // Agora, as chaves 'startDate' e 'endDate' devem estar acessíveis com segurança em $currentFormData
        $startDate = Carbon::parse($currentFormData['startDate']);
        $endDate = Carbon::parse($currentFormData['endDate']);

        $this->reportData = ChartOfAccount::query()
            ->whereNull('parent_uuid')
            ->with(['childAccounts']) // Certifique-se de que o modelo ChartOfAccount carrega childAccounts recursivamente
            ->orderBy('code')
            ->get();

        $this->generateMonthHeaders($startDate, $endDate);
    }

    protected function generateMonthHeaders(Carbon $startDate, Carbon $endDate): void
    {
        $this->monthHeaders = [];
        $currentMonth = $startDate->copy()->startOfMonth();
        $finalMonth = $endDate->copy()->startOfMonth();

        while ($currentMonth->lte($finalMonth)) {
            $this->monthHeaders[] = $currentMonth->copy();
            $currentMonth->addMonthNoOverflow();
        }
    }

    protected function getValidatedFilterDates(): array
    {
        // 1. Executa a validação
        $this->form->validate(); // Lançará ValidationException se falhar

        // 2. Usa $this->data como a fonte dos dados validados
        $currentFormData = $this->data;

        // 3. Verificação defensiva
        if (
            !isset($currentFormData['startDate']) || empty($currentFormData['startDate']) ||
            !isset($currentFormData['endDate']) || empty($currentFormData['endDate'])
        ) {
            Log::error('FinancialDashboard (getValidatedFilterDates): CRITICAL - Form state ($this->data) is missing required date keys or they are empty, after validation.', [
                'current_form_data_property' => $currentFormData,
            ]);
            throw new \LogicException('O estado do formulário está faltando chaves de data obrigatórias em getValidatedFilterDates após a validação.');
        }

        $startDate = Carbon::parse($currentFormData['startDate']);
        $endDate = Carbon::parse($currentFormData['endDate']);
        return [$startDate, $endDate];
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Imprimir PDF')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(route('financial.report.pdf', [
                    'start_date' => Carbon::parse($this->data['startDate'])->format('Y-m-d'),
                    'end_date' => Carbon::parse($this->data['endDate'])->format('Y-m-d'),
                ]), shouldOpenInNewTab: true),
        ];
    }
}
