<?php

namespace App\Filament\Resources\PricingTableResource\Pages;

use App\Filament\Resources\PricingTableResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePricingTable extends CreateRecord
{
    protected static string $resource = PricingTableResource::class;
    
    public static function getCreateButtonLabel(): string
        {
            return 'Nova tabela de preços';
        }
}
