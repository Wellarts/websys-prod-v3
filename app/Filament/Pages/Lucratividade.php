<?php

namespace App\Filament\Pages;

use App\Models\ItensVenda;
use App\Models\Venda;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class Lucratividade extends Page implements HasTable
{

    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.lucratividade';

    protected static ?string $navigationGroup = 'Consultas';


    public function mount()
    {

        $vendas = Venda::all();

        foreach ($vendas as $venda) {

            $itensVenda = ItensVenda::where('venda_id', $venda->id)->get();

            foreach ($itensVenda as $itens) {
                $custo_venda = +$itens->total_custo_atual;
                // dd($custo_venda);

            }

            $venda->lucro_venda = ($venda->valor_total - $custo_venda);
            $venda->save();
        }
    }

   
    public function table(Table $table): Table
    {
        return $table
            ->query(Venda::query())
          //  ->defaultGroup('data_venda','year')
            ->columns([
                TextColumn::make('id')
                    ->alignCenter()
                    ->label('Venda'),
                TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('data_venda')
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('itens_venda_sum_total_custo_atual')->sum('itensVenda', 'total_custo_atual')
                    ->badge()
                    ->alignCenter()
                    ->label('Custo Produtos')
                    ->money('BRL')
                    ->color('danger'),
                TextColumn::make('valor_total')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->badge()
                    ->alignCenter()
                    ->label('Valor da Venda')
                    ->money('BRL')
                    ->color('warning'),
                TextColumn::make('lucro_venda')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->badge()
                    ->alignCenter()
                    ->label('Lucro por Venda')
                    ->money('BRL')
                    ->color('success')
                    ->getStateUsing(function (Venda $record): float {
                        $custoProdutos = $record->itensVenda()->sum('total_custo_atual');
                        return ($record->valor_total - $custoProdutos);
                    })


            ])
            ->filters([
                SelectFilter::make('cliente')->relationship('cliente', 'nome'),

                Filter::make('data_vencimento')
                    ->form([
                        DatePicker::make('venda_de')
                            ->label('Data da Venda de:'),
                        DatePicker::make('venda_ate')
                            ->label('Data da Venda até:'),
                    ])
                    ->query(function ($query, array $data) {
                        $query
                            ->when(
                                $data['venda_de'],
                                fn ($query) => $query->whereDate('data_venda', '>=', $data['venda_de'])
                            )
                            ->when(
                                $data['venda_ate'],
                                fn ($query) => $query->whereDate('data_venda', '<=', $data['venda_ate'])
                            );
                    })
            ]);
    }
}
