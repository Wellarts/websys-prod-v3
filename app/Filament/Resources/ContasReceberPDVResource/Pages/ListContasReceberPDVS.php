<?php

namespace App\Filament\Resources\ContasReceberPDVResource\Pages;

use App\Filament\Resources\ContasReceberPDVResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContasReceberPDVS extends ListRecords
{
    protected static string $resource = ContasReceberPDVResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
