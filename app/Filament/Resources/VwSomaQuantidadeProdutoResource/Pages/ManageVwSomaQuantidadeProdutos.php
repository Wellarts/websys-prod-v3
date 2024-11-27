<?php

namespace App\Filament\Resources\VwSomaQuantidadeProdutoResource\Pages;

use App\Filament\Resources\VwSomaQuantidadeProdutoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVwSomaQuantidadeProdutos extends ManageRecords
{
    protected static string $resource = VwSomaQuantidadeProdutoResource::class;

    protected static ?string $title = 'Rentabilidade Produto/Serviço';

    protected function getHeaderActions(): array
    {
        return [
       //     Actions\CreateAction::make(),
        ];
    }
}
