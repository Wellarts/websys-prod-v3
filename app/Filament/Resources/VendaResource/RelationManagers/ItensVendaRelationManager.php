<?php

namespace App\Filament\Resources\VendaResource\RelationManagers;

use App\Models\ItensVenda;
use App\Models\Produto;
use App\Models\Venda;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItensVendaRelationManager extends RelationManager
{
    protected static string $relationship = 'ItensVenda';

    protected static ?string $title = 'Itens da Venda';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('id'),

                Forms\Components\Hidden::make('venda_id')
                    ->default((function ($livewire): int {
                        return $livewire->ownerRecord->id;
                    })),

                Forms\Components\Select::make('produto_id')
                    ->options(Produto::all()->pluck('nome', 'id')->toArray())
                    ->disableOptionWhen(fn ($context) => $context == 'edit')
                    ->searchable()
                    ->reactive()
                    ->required()
                    ->label('Produto')
                    ->afterStateUpdated(
                        function ($state, callable $set, Get $get,) {
                            $produto = Produto::find($state);

                            if ($produto) {
                                $set('valor_venda', $produto->valor_venda);
                                $set('valor_custo_atual', $produto->valor_compra);
                                $set('sub_total', (($get('qtd') * $get('valor_venda')) + (float)$get('acres_desc')));
                                $set('estoque_atual', $produto->estoque);
                                $set('total_custo_atual', $get('valor_custo_atual') * $get('qtd'));
                            }
                        }
                    ),
                Forms\Components\TextInput::make('estoque_atual')
                    ->hidden(fn (string $context): bool => $context === 'edit')
                    ->readOnly(),

                Forms\Components\TextInput::make('qtd')
                    ->default('1')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(
                        function ($state, callable $set, Get $get,) {
                            $set('sub_total', (((float)$get('qtd') * (float)$get('valor_venda')) + (float)$get('acres_desc')));
                            $set('total_custo_atual', $get('valor_custo_atual') * $get('qtd'));
                        }
                    ),
                Forms\Components\TextInput::make('valor_venda')
                    ->required()
                    ->readOnly(),
                Forms\Components\TextInput::make('acres_desc')
                    ->label('Desconto/Acréscimo')
                    ->reactive()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('sub_total', (((float)$get('qtd') * (float)$get('valor_venda')) + (float)$get('acres_desc')));
                    }),
                Forms\Components\TextInput::make('sub_total')
                    ->readOnly()
                    ->label('SubTotal'),
                Forms\Components\Hidden::make('valor_custo_atual'),
                Forms\Components\Hidden::make('total_custo_atual'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('venda_id')
            ->columns([
                Tables\Columns\TextColumn::make('produto.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('qtd')
                    ->summarize(Count::make()->label('Qtd de Produtos')),
                Tables\Columns\TextColumn::make('valor_venda')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('acres_desc')
                    ->label('Desconto/Acréscimo')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('sub_total')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->money('BRL'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->modalHeading('Itens da Venda')
                    ->label('Adicionar')
                    ->after(function ($data, $record) {
                        $produto = Produto::find($data['produto_id']);
                        $produto->estoque -= $data['qtd'];
                        $venda = Venda::find($data['venda_id']);
                        $venda->valor_total += $data['sub_total'];
                        $venda->save();
                        $produto->save();
                    })

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->before(function ($data) {

                        $produto = Produto::find($data['produto_id']);
                        $idItemCompra = ItensVenda::find($data['id']);
                        $venda = Venda::find($data['venda_id']);
                        $produto->estoque -= ($data['qtd'] - $idItemCompra->qtd);
                        $venda->valor_total += ($data['sub_total'] - $idItemCompra->sub_total);
                        $venda->save();
                        $produto->save();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($data, $record) {
                        $produto = Produto::find($record->produto_id);
                        $venda = Venda::find($record->venda_id);
                        $venda->valor_total -= $record->sub_total;
                        $produto->estoque += ($record->qtd);
                        $venda->save();
                        $produto->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
