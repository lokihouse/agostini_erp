<?php

namespace App\Filament\Actions;

class ExportAction extends \Filament\Actions\ExportAction
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->label("");
        $this->icon("fas-download");
        $this->color("primary");
        $this->iconButton();
    }
}
