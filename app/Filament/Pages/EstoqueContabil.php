<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ProdutoResource;
use App\Models\Produto;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EstoqueContabil extends Page  implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

   // protected static string $resource = ProdutoResource::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.estoque-contabil';

    protected function getTableQuery(): Builder
    {
        return Produto::query();
    }

    protected function getTableColumns(): array
    {
        return [
                TextColumn::make('nome')
                    ->label('Produto')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('estoque')
                    ->alignCenter(),
                TextColumn::make('valor_compra')
                    ->money('BRL'),
                TextColumn::make('lucratividade')
                    ->alignCenter()
                    ->label('Lucratividade (%)'),
                TextColumn::make('valor_venda')
                    ->alignCenter()
                    ->money('BRL'),
                TextColumn::make('total_compra')
                    ->summarize(Sum::make()->label('Total'))
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                        return (($record->estoque * $record->valor_compra)*100);
                })
                    ->money('BRL')
                    ->color('danger'),
                TextColumn::make('total_venda')
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                    return ($record->estoque * $record->valor_venda)*100;
                })
                    ->money('BRL')
                    ->color('warning'),
                TextColumn::make('total_lucratividade')
                    ->alignCenter()
                    ->getStateUsing(function (Produto $record): float {
                         return ((($record->estoque * $record->valor_venda)*100) - (($record->estoque * $record->valor_compra)*100));
                })
                    ->color('success')
                    ->money('BRL'),
                
        ];
    }
}
