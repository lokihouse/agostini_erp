<?php

namespace App\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;

class ProgressBarColumn extends Column
{
    protected string $view = 'tables.columns.progress-bar-column';
    protected string | Closure $status = '';

    public function status(): string | Closure
    {
        return $this->status;
    }
}
