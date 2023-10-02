<?php

namespace App\Filament\Resources\ContasReceberResource\Pages;

use App\Filament\Resources\ContasReceberResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageContasRecebers extends ManageRecords
{
    protected static string $resource = ContasReceberResource::class;

    protected static ?string $title = 'Contas a Receber/Recebidas';

    protected function getHeaderActions(): array
    {
        return [
        //    Actions\CreateAction::make(),
                
        ];
    }
}
