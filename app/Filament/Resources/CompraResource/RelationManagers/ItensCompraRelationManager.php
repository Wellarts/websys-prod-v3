<?php

namespace App\Filament\Resources\CompraResource\RelationManagers;

use App\Models\Compra;
use App\Models\ItensCompra;
use App\Models\Produto;
use App\Models\ProdutoFornecedor;
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

class ItensCompraRelationManager extends RelationManager
{
    protected static string $relationship = 'ItensCompra';

    protected static ?string $recordTitleAttribute = 'compra_id';

    protected static ?string $title = 'Itens da Compra';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('id')
                    ->disabled(),
                    Forms\Components\Hidden::make('compra_id')
                    ->default((function ($livewire): int {
                        return $livewire->ownerRecord->id;
                    })),
                    
                Forms\Components\Select::make('produto_id')
                    ->relationship(name: 'produto', titleAttribute: 'nome')
                    ->searchable(['nome', 'codbar'])
                 //  ->options(Produto::all()->pluck('nome', 'id')->toArray())
                    ->disabled(fn ($context) => $context == 'edit')
                    ->live(debounce:200)
                    ->native(false)
                    ->required()
                    ->label('Produto')
                    ->afterStateUpdated(function ($state, callable $set) {
                        $produto = Produto::find($state);
                        if($produto) {
                            $set('valor_compra', $produto->valor_compra);
                        }

                    }
                ), 
                Forms\Components\TextInput::make('valor_compra')
                    ->label('Valor Compra')
                    ->live(onBlur:true)
                    ->required()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('sub_total', (($get('qtd') * $get('valor_compra'))));
                    }
                ),   
                Forms\Components\TextInput::make('qtd')
                    ->required()
                    ->live(onBlur:true)
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $set('sub_total', (($get('qtd') * $get('valor_compra'))));
                    }),
                Forms\Components\TextInput::make('sub_total')
                    ->readOnly()
                    ->label('Sub-Total'),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('compra_id')
            ->columns([
                Tables\Columns\TextColumn::make('produto.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('qtd')
                    ->summarize(Sum::make()->label('Qtd de Produtos')),
                Tables\Columns\TextColumn::make('valor_compra')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('sub_total')
                    ->summarize(Sum::make()->money('BRL')->label('Total'))
                    ->money('BRL'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->label('Adicionar')
                    ->before(function ($data) {
                        $produto = Produto::find($data['produto_id']);
                        $compra = Compra::find($data['compra_id']);
                        $produto->estoque += $data['qtd'];
                        $produto->valor_compra = $data['valor_compra'];
                        $produto->valor_venda = ($produto->valor_compra + ($data['valor_compra'] * ($produto->lucratividade / 100)));
                        $compra->valor_total += $data['sub_total'];
                        $compra->save();
                        $produto->save();

                        $prodFornecedor = [
                            'compra_id' => $data['compra_id'],
                            'produto_id' => $produto->id,
                          
                            ];
                        ProdutoFornecedor::create($prodFornecedor);
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->before(function ($data, $record) {
                    $produto = Produto::find($record->produto_id);
                    $idItemCompra = ItensCompra::find($record->id);
                    $compra = Compra::find($record->compra_id);
             //   dd( $data['qtd']);
                    $produto->estoque += ($data['qtd'] - $idItemCompra->qtd);
                    $produto->valor_compra = $record->valor_compra;
                    $produto->valor_venda = ($produto->valor_compra + ($record->valor_compra * ($produto->lucratividade / 100)));
                    $compra->valor_total += ($data['sub_total'] - $idItemCompra->sub_total);
                  // dd($data['sub_total'], $idItemCompra->sub_total,  $compra->valor_total);
                    $compra->save();
                    $produto->save();
                }),
            Tables\Actions\DeleteAction::make()
             ->before(function ($data, $record) {
                  $produto = Produto::find($record->produto_id);
                  $compra = Compra::find($record->compra_id);
                  $compra->valor_total -= $record->sub_total;
                  $produto->estoque -= ($record->qtd);
                  $produto->save();
                  $compra->save();

                  $prodFornecedor = [
                    'compra_id' => $record->compra_id,
                    'produto_id' => $produto->id,
                  
                    ];
                ProdutoFornecedor::destroy($prodFornecedor);
                }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
