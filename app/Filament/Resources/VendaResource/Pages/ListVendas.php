<?php

namespace App\Filament\Resources\VendaResource\Pages;

use App\Filament\Resources\VendaResource;
use App\Livewire\VendaStatsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendas extends ListRecords
{
    protected static string $resource = VendaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
         /** @var \App\Models\User */
        //   $authUser =  auth()->user();

       /* if($authUser->hasRole('Administrador'))
         { */
            return [  
                VendaStatsOverview::class

            ];
       /*   }
         else
        {
            return [

            ];
        } */
    }
}
