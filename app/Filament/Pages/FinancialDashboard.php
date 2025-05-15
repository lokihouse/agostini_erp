<?php

namespace App\Filament\Pages;

use App\Models\ChartOfAccount;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification; // Import Notification
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder; // Import Builder
use Illuminate\Support\Facades\DB; // Import DB
use Illuminate\Support\Facades\Log; // Import Log
use Illuminate\Validation\ValidationException; // Import ValidationException

// If you create an exporter:
// use App\Filament\Exports\FinancialDashboardExporter;
// use Filament\Actions\Exports\Enums\ExportFormat;
// use Filament\Actions\ExportAction;


class FinancialDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    public array $data = []; // Property to hold form state

    public Collection $reportData;
    public array $monthHeaders = [];

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Financeiro';
    protected static ?int $navigationSort = 51;
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
                ->minDate(fn (callable $get) => $get('startDate'))
                ->required()
                ->afterStateUpdated(fn (FinancialDashboard $livewire) => $livewire->updateReportData()),
        ];
    }

    protected function updateReportData(): void
    {
        $validatedData = [];
        try {
            // This will validate $this->data based on the schema defined in getFormSchema()
            $validatedData = $this->form->validate();
        } catch (ValidationException $e) {
            Log::warning('FinancialDashboard: Validation failed.', ['errors' => $e->errors(), 'form_data' => $this->data]);
            $this->reportData = collect();
            $this->monthHeaders = [];
            // Notification for validation errors is usually handled by Filament automatically.
            return;
        }

        // Defensive check: Ensure required keys are present after successful validation
        if (!isset($validatedData['startDate']) || !isset($validatedData['endDate'])) {
            Log::error('FinancialDashboard: CRITICAL - Validation passed but required date keys are missing.', [
                'validated_data' => $validatedData,
                'form_data_property_at_time_of_error' => $this->data
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

        // Keys 'startDate' and 'endDate' should now be safely accessible
        $startDate = Carbon::parse($validatedData['startDate']);
        $endDate = Carbon::parse($validatedData['endDate']);

        $this->reportData = ChartOfAccount::query()
            ->whereNull('parent_uuid')
            ->with(['childAccounts']) // Ensure ChartOfAccount model eager loads childAccounts recursively
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
        // This will validate $this->data
        $validatedData = $this->form->validate(); // This will throw ValidationException if fails

        // Add the same defensive check here if this method is used independently
        if (!isset($validatedData['startDate']) || !isset($validatedData['endDate'])) {
            Log::error('FinancialDashboard (getValidatedFilterDates): CRITICAL - Validation passed but required date keys are missing.', [
                'validated_data' => $validatedData,
                'form_data_property_at_time_of_error' => $this->data
            ]);
            // Decide how to handle this: throw an exception, or return default/null dates?
            // For now, let's re-throw or throw a new specific exception if this happens.
            throw new \LogicException('Validated data is missing required date keys in getValidatedFilterDates.');
        }

        $startDate = Carbon::parse($validatedData['startDate']);
        $endDate = Carbon::parse($validatedData['endDate']);
        return [$startDate, $endDate];
    }


    protected function getHeaderActions(): array
    {
        // Example Export Action
        // if (class_exists(FinancialDashboardExporter::class)) {
        //     try {
        //         [$startDate, $endDate] = $this->getValidatedFilterDates();
        //         return [
        //             ExportAction::make()
        //                 ->label('Exportar Relatório')
        //                 ->exporter(FinancialDashboardExporter::class)
        //                 ->formats([ExportFormat::Xlsx, ExportFormat::Csv])
        //                 ->fileName(fn (): string => 'resumo-financeiro-' . now()->format('Y-m-d'))
        //                 ->getExporterOptionsUsing(fn () => [
        //                     'startDate' => $startDate,
        //                     'endDate' => $endDate,
        //                 ]),
        //         ];
        //     } catch (ValidationException $e) {
        //         // Validation failed when trying to get dates for export
        //         return [];
        //     } catch (\LogicException $e) {
        //         // Catch the custom exception from getValidatedFilterDates
        //         Log::error($e->getMessage());
        //         Notification::make()->danger()->title('Erro ao Preparar Exportação')->body('Datas de filtro inválidas.')->send();
        //         return [];
        //     }
        // }
        return [];
    }
}
