<?php

namespace App\Filament\Resources\FormaPgmtoResource\Pages;

use App\Filament\Resources\FormaPgmtoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFormaPgmtos extends ManageRecords
{
    protected static string $resource = FormaPgmtoResource::class;

    protected static ?string $title = 'Formas de Pagamento';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo')
                ->modalHeading('Criar forma de pagamento'),
        ];
    }
}
