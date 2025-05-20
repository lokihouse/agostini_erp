<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

class GenerateFilamentImportExportAll extends Command
{
    protected $signature = 'filament:generate-import-export-all {--overwrite : Overwrite existing files}';
    protected $description = 'Generate basic Exporter and Importer classes for all Filament Resources';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $this->info('Starting generation of Importer/Exporter classes...');

        $resourcePath = app_path('Filament/Resources');
        if (!$this->files->isDirectory($resourcePath)) {
            $this->error('Filament Resources path not found: ' . $resourcePath);
            return self::FAILURE;
        }

        $exportPath = app_path('Filament/Exports');
        $importPath = app_path('Filament/Imports');

        if (!$this->files->isDirectory($exportPath)) {
            $this->files->makeDirectory($exportPath, 0755, true);
        }
        if (!$this->files->isDirectory($importPath)) {
            $this->files->makeDirectory($importPath, 0755, true);
        }

        $finder = new Finder();
        $finder->files()->in($resourcePath)->name('*Resource.php');

        if (!$finder->hasResults()) {
            $this->warn('No Filament Resource files found in ' . $resourcePath);
            return self::SUCCESS;
        }

        foreach ($finder as $file) {
            $resourceClassName = $this->getClassNameFromFile($file->getRealPath());

            if (!$resourceClassName || !class_exists($resourceClassName)) {
                $this->warn("Could not determine class name or class does not exist for file: " . $file->getFilename());
                continue;
            }

            try {
                $reflection = new ReflectionClass($resourceClassName);
                if (!$reflection->isSubclassOf(\Filament\Resources\Resource::class) || $reflection->isAbstract()) {
                    continue;
                }

                $modelClass = call_user_func([$resourceClassName, 'getModel']);
                if (!$modelClass || !class_exists($modelClass)) {
                    $this->warn("Could not get model for resource: {$resourceClassName}");
                    continue;
                }

                $modelInstance = new $modelClass();
                $modelName = class_basename($modelClass);
                $fillable = $modelInstance->getFillable(); // Can be empty if guarded = ['*']

                $this->generateExporter($modelName, $modelClass, $fillable, $exportPath);
                $this->generateImporter($modelName, $modelClass, $fillable, $importPath);

            } catch (\Throwable $e) {
                $this->error("Error processing resource {$resourceClassName}: " . $e->getMessage());
            }
        }

        $this->info('------------------------------------------------------------------');
        $this->info('Importer/Exporter generation complete!');
        $this->line('Next steps:');
        $this->line('1. Review the generated classes in `app/Filament/Exports` and `app/Filament/Imports`.');
        $this->line('2. Customize columns, rules, and `resolveRecord()` methods as needed.');
        $this->line('3. Register the Exporters and Importers in your Resource actions.');
        $this->line('   Example for ExportAction: `ExportAction::make()->exporter(' . $modelName . 'Exporter::class)`');
        $this->line('   Example for ImportAction: `ImportAction::make()->importer(' . $modelName . 'Importer::class)`');
        $this->info('------------------------------------------------------------------');

        return self::SUCCESS;
    }

    protected function getClassNameFromFile(string $filePath): ?string
    {
        $contents = $this->files->get($filePath);
        $namespace = $class = '';

        if (preg_match('#^namespace\s+(.+?);$#sm', $contents, $matches)) {
            $namespace = $matches[1];
        }

        if (preg_match('#^class\s+(\w+)\s+extends#sm', $contents, $matches)) {
            $class = $matches[1];
        }

        if (!empty($namespace) && !empty($class)) {
            return $namespace . '\\' . $class;
        }
        return null;
    }

    protected function generateExporter(string $modelName, string $modelClass, array $fillable, string $path): void
    {
        $exporterName = "{$modelName}Exporter";
        $filePath = "{$path}/{$exporterName}.php";

        if (!$this->option('overwrite') && $this->files->exists($filePath)) {
            $this->warn("Exporter already exists, skipping: {$filePath}");
            return;
        }

        $columns = [
            "ExportColumn::make('id')->label('ID')",
        ];
        foreach ($fillable as $attribute) {
            $label = Str::headline($attribute);
            $columns[] = "            ExportColumn::make('{$attribute}')->label('{$label}')";
        }
        $columnsString = implode(",\n", $columns);
        $modelNamePluralSnake = Str::snake(Str::pluralStudly($modelName));

        $stub = <<<PHP
<?php

namespace App\Filament\Exports;

use {$modelClass};
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class {$exporterName} extends Exporter
{
    protected static ?string \$model = {$modelName}::class;

    public static function getColumns(): array
    {
        return [
{$columnsString}
        ];
    }

    public static function getCompletedNotificationBody(Export \$export): string
    {
        \$body = 'Your {$modelName} export has completed and ' . number_format(\$export->successful_rows) . ' ' . Str::plural('row', \$export->successful_rows) . ' exported.';

        if (\$failedRowsCount = \$export->getFailedRowsCount()) {
            \$body .= ' ' . number_format(\$failedRowsCount) . ' ' . Str::plural('row', \$failedRowsCount) . ' failed to export.';
        }

        return \$body;
    }

    public function getFileName(Export \$export): string // Added for user convenience
    {
        return '{$modelNamePluralSnake}_' . \$export->getKey() . '.xlsx';
    }

    // Optional: You can define form components for exporter options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('include_extra_data')->label('Include Extra Data'),
    //     ];
    // }
}
PHP;
        $this->files->put($filePath, $stub);
        $this->info("Generated Exporter: {$filePath}");
    }

    protected function generateImporter(string $modelName, string $modelClass, array $fillable, string $path): void
    {
        $importerName = "{$modelName}Importer";
        $filePath = "{$path}/{$importerName}.php";

        if (!$this->option('overwrite') && $this->files->exists($filePath)) {
            $this->warn("Importer already exists, skipping: {$filePath}");
            return;
        }

        $columns = [];
        foreach ($fillable as $attribute) {
            $label = Str::headline($attribute);
            // Basic rule, you'll likely want to customize this
            $columns[] = "            ImportColumn::make('{$attribute}')\n                ->label('{$label}')\n                ->requiredMapping()\n                ->rules(['nullable', 'max:255'])";
        }
        // Add a common 'id' column for potential updates, but make it optional for mapping
        array_unshift($columns, "            ImportColumn::make('id')\n                ->label('ID (for updates)')\n                ->rules(['nullable', 'integer'])");

        $columnsString = implode(",\n", $columns);

        $stub = <<<PHP
<?php

namespace App\Filament\Imports;

use {$modelClass};
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\TextInput; // Example, adjust as needed
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash; // If you have password fields

class {$importerName} extends Importer
{
    protected static ?string \$model = {$modelName}::class;

    public static function getColumns(): array
    {
        return [
{$columnsString}
        ];
    }

    public function resolveRecord(): ?{$modelName}
    {
        // Basic example: always creates a new record.
        // Customize this method to update existing records if needed.
        //
        // Example for updating or creating:
        // if (\$this->data['id'] ?? null) {
        //      \$record = {$modelName}::find(\$this->data['id']);
        //      if (\$record) {
        //          return \$record;
        //      }
        // }
        //
        // // Or using a unique business key:
        // // return {$modelName}::firstOrNew([
        // //     'email' => \$this->data['email'],
        // // ]);

        return new {$modelName}();
    }

    public static function getCompletedNotificationBody(Import \$import): string
    {
        \$body = 'Your {$modelName} import has completed and ' . number_format(\$import->successful_rows) . ' ' . Str::plural('row', \$import->successful_rows) . ' imported.';

        if (\$failedRowsCount = \$import->getFailedRowsCount()) {
            \$body .= ' ' . number_format(\$failedRowsCount) . ' ' . Str::plural('row', \$failedRowsCount) . ' failed to import.';
        }

        return \$body;
    }

    // Optional: You can define form components for importer options
    // public static function getOptionsFormComponents(): array
    // {
    //     return [
    //         // Forms\Components\Checkbox::make('update_existing')->label('Update existing records'),
    //     ];
    // }

    // Optional: Define a global data validation schema if needed
    // protected function getValidationRules(): array
    // {
    //    return [
    //        // '*.email' => ['required', 'email', 'unique:'.{$modelName}::class.',email'],
    //    ];
    // }

    // Optional: Process data before saving, e.g. hashing passwords
    // protected function beforeSave(array \$data): array
    // {
    //     if (isset(\$data['password'])) {
    //         \$data['password'] = Hash::make(\$data['password']);
    //     }
    //     return \$data;
    // }
}
PHP;
        $this->files->put($filePath, $stub);
        $this->info("Generated Importer: {$filePath}");
    }
}
