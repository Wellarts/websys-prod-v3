<?php

namespace App\Filament\Resources\CompraResource\RelationManagers;

use App\Models\contasPagar;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContasPagarRelationManager extends RelationManager
{
    protected static string $relationship = 'ContasPagar';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('4')
                    ->schema([
                        Forms\Components\TextInput::make('compra_id')
                            ->hidden()
                            ->required(),
                        Forms\Components\Select::make('fornecedor_id')
                            ->columnSpan([
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                            ->label('Fornecedor')
                            ->default((function ($livewire): int {
                                return $livewire->ownerRecord->fornecedor_id;
                            }))

                            ->options(function (RelationManager $livewire): array {
                                return $livewire->ownerRecord
                                    ->fornecedor()
                                    ->pluck('nome', 'id')
                                    ->toArray();
                            })
                            ->required(),
                        Forms\Components\TextInput::make('ordem_parcela')
                            ->label('Parcela Nº')
                            ->readOnly()
                            ->default('1')
                            ->required(),
                        Forms\Components\TextInput::make('parcelas')
                            ->default('1')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('parcelas') != 1) {
                                    $set('valor_parcela', (($get('valor_total') / $get('parcelas'))));
                                    $set('status', 0);
                                    $set('valor_pago', 0);
                                    $set('data_pagamento', null);
                                    $set('data_vencimento',  Carbon::now()->addDays(30)->format('Y-m-d'));
                                } else {
                                    $set('valor_parcela', $get('valor_total'));
                                    $set('status', 1);
                                    $set('valor_pago', $get('valor_total'));
                                    $set('data_pagamento', Carbon::now()->format('Y-m-d'));
                                    $set('data_vencimento',  Carbon::now()->format('Y-m-d'));
                                }
                            })
                            ->required(),



                        Forms\Components\DatePicker::make('data_vencimento')
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->label("Data do Vencimento")
                            ->required(),
                        Forms\Components\DatePicker::make('data_pagamento')
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->label("Data do Pagamento"),
                        Forms\Components\TextInput::make('valor_total')
                            ->label('Valor Total')
                            ->default((function ($livewire): float {
                                return $livewire->ownerRecord->valor_total;
                            }))
                            ->readOnly()
                            ->required(),

                        Forms\Components\TextInput::make('valor_parcela')
                            ->label('Valor da Parcela ')
                            ->default((function ($livewire): float {
                                return $livewire->ownerRecord->valor_total;
                            }))
                            ->required()
                            ->readOnly(),
                        Forms\Components\TextInput::make('valor_pago')
                            ->label('Valor Pago')
                            ->default((function ($livewire): float {
                                return $livewire->ownerRecord->valor_total;
                            })),
                        Forms\Components\Textarea::make('obs')
                            ->columnSpan([
                                'xl' => 3,
                                '2xl' => 3,
                            ])
                            ->label('Observações'),
                        Forms\Components\Toggle::make('status')
                            ->inlineLabel(false)
                            ->default('true')
                            ->label('Pago')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(
                                function (Get $get, Set $set) {
                                    if ($get('status') == 1) {
                                        $set('valor_pago', $get('valor_parcela'));
                                        $set('data_pagamento', Carbon::now()->format('Y-m-d'));
                                    } else {

                                        $set('valor_pago', 0);
                                        $set('data_pagamento', null);
                                    }
                                }
                            ),

                    ])

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('compra_id')
            ->columns([
                Tables\Columns\TextColumn::make('fornecedor.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ordem_parcela')
                    ->alignCenter()
                    ->label('Parcela Nº'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Data do Vencimento')
                    ->badge()
                    ->color('warning')
                    ->label('Valor Total')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('data_vencimento')
                    ->label('Data do Vencimento')
                    ->badge()
                    ->color('danger')
                    ->sortable()
                    ->date(),

                Tables\Columns\TextColumn::make('valor_parcela')
                    ->badge()
                    ->color('danger')
                    ->label('Valor da Parcela')
                    ->money('BRL'),
                Tables\Columns\IconColumn::make('status')
                    ->alignCenter()
                    ->label('Pago')
                    ->boolean(),
                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Data do Pagamento')
                    ->badge()
                    ->color('success')
                    ->date(),
                Tables\Columns\TextColumn::make('valor_pago')
                    ->badge()
                    ->color('success')
                    ->label('Valor Pago'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Lançar Pagamento')
                    ->after(
                        function ($data, $record) {
                            if ($record->parcelas > 1) {
                                $valor_parcela = ($record->valor_total / $record->parcelas);
                                $vencimentos = Carbon::create($record->data_vencimento);
                                for ($cont = 1; $cont < $data['parcelas']; $cont++) {
                                    $dataVencimentos = $vencimentos->addDays(30);
                                    $parcelas = [
                                        'compra_id' => $record->compra_id,
                                        'fornecedor_id' => $data['fornecedor_id'],
                                        'valor_total' => $data['valor_total'],
                                        'parcelas' => $data['parcelas'],
                                        'ordem_parcela' => $cont + 1,
                                        'data_vencimento' => $dataVencimentos,
                                        'valor_pago' => 0.00,
                                        'status' => 0,
                                        'obs' => $data['obs'],
                                        'valor_parcela' => $valor_parcela,
                                    ];
                                    contasPagar::create($parcelas);
                                }
                            } else {
                                $addFluxoCaixa = [
                                    'valor' => ($record->valor_total * -1),
                                    'tipo'  => 'DEBITO',
                                    'obs'   => 'Pagamento da compra nº: ' . $record->compra_id . '',
                                ];

                                FluxoCaixa::create($addFluxoCaixa);
                            }
                        }
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($data, $record) {

                        if ($record->status = 1) {
                            $addFluxoCaixa = [
                                'valor' => ($record->valor_parcela * -1),
                                'tipo'  => 'DEBITO',
                                'obs'   => 'Pagamento da compra nº: ' . $record->compra_id . '',
                            ];

                            FluxoCaixa::create($addFluxoCaixa);
                        }
                    }),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
