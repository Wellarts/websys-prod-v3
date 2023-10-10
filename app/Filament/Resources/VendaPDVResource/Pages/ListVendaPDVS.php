<?php

namespace App\Filament\Resources\VendaPDVResource\Pages;

use App\Filament\Resources\VendaPDVResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendaPDVS extends ListRecords
{
    protected static string $resource = VendaPDVResource::class;

    protected static ?string $title = 'Vendas PDV';

    protected function getHeaderActions(): array
    {
        return [
           // Actions\CreateAction::make()
           //     ->modalHeading('Vendas PDV'),
        ];
    }
}
