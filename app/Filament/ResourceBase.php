<?php

namespace App\Filament;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

class ResourceBase extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->columns(10)
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}
