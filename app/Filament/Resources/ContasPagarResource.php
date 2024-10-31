<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContasPagarResource\Pages;
use App\Filament\Resources\ContasPagarResource\RelationManagers;
use App\Models\ContasPagar;
use App\Models\FluxoCaixa;
use App\Models\Fornecedor;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContasPagarResource extends Resource
{
    protected static ?string $model = ContasPagar::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-trending-down';

    protected static ?string $navigationLabel = 'Contas a Pagar';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('4')
                    ->schema([
                        Forms\Components\Select::make('fornecedor_id')
                            ->columnSpan([
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                            ->label('Fornecedor')
                            ->options(Fornecedor::all()->pluck('nome', 'id')->toArray())
                            ->required()
                            ->disabled(function ($context) {
                                if ($context == 'create') {
                                    return false;
                                } else {
                                    return true;
                                }
                            }),
                        Forms\Components\TextInput::make('compra_id')
                            ->hidden()
                            ->required(),
                        Forms\Components\TextInput::make('ordem_parcela')
                            ->label('Parcela Nº')
                            ->default(1)
                            ->readOnly()
                            // ->hidden(function ($context) {
                            //     if ($context == 'edit') {
                            //         return false;
                            //     } else {
                            //         return true;
                            //     }
                            // })
                            ->maxLength(10),
                        Forms\Components\TextInput::make('valor_parcela')
                            ->numeric()
                            ->label('Valor da Parcela')
                            ->readOnly(function ($context) {
                                if ($context == 'create') {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            
                            ->required(),
                        Forms\Components\TextInput::make('parcelas')
                            ->label('Qtd Parcelas')
                            ->required()
                            ->live(onBlur:true)
                            ->readOnly(function ($context) {
                                if ($context == 'create') {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            ->afterStateUpdated(function($state, Set $set, Get $get){
                                $set('valor_total', ($get('valor_parcela') * $state));
                            })
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('data_vencimento')
                            ->label('Data do Vencimento')
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Forms\Components\DatePicker::make('data_pagamento')
                            ->label('Data do Pagamento')
                            ->hidden(function ($context) {
                                if ($context == 'edit') {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            ->displayFormat('d/m/Y'),

                        Forms\Components\TextInput::make('valor_total')
                            ->numeric()
                            ->label('Valor Total')
                            ->readOnly(function ($context) {
                                if ($context == 'create') {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            ->required(),

                        Forms\Components\TextInput::make('valor_pago')
                            ->numeric()
                            ->hidden(function ($context) {
                                if ($context == 'edit') {
                                    return false;
                                } else {
                                    return true;
                                }
                            })
                            ->label('Valor Pago'),
                        Forms\Components\Textarea::make('obs')
                            ->columnSpan([
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                            ->label('Observações'),
                    ]),
                Forms\Components\Toggle::make('status')
                    /* ->columnSpan([
                            'xl' => 3,
                            '2xl' => 3,
                        ]) */
                    ->inlineLabel(false)
                    ->default(0)
                    ->label('Pago')
                    ->required()
                    ->live()
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


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('data_vencimento', 'asc')
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
                    ->summarize(Sum::make()->money('BRL')->label('Total Parcelas'))
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
                    ->summarize(Sum::make()->money('BRL')->label('Total Pago'))
                    ->badge()
                    ->color('success')
                    ->label('Valor Pago'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('A pagar')
                    ->query(fn(Builder $query): Builder => $query->where('status', false))->default(true),
                Filter::make('Pagas')
                    ->query(fn(Builder $query): Builder => $query->where('status', true)),
                SelectFilter::make('fornecedor')->relationship('fornecedor', 'nome')->searchable(),
                Tables\Filters\Filter::make('data_vencimento')
                    ->form([
                        Forms\Components\DatePicker::make('vencimento_de')
                            ->label('Vencimento de:'),
                        Forms\Components\DatePicker::make('vencimento_ate')
                            ->label('Vencimento até:'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['vencimento_de'],
                                fn($query) => $query->whereDate('data_vencimento', '>=', $data['vencimento_de'])
                            )
                            ->when(
                                $data['vencimento_ate'],
                                fn($query) => $query->whereDate('data_vencimento', '<=', $data['vencimento_ate'])
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(function ($data, $record) {

                        if ($record->status = 1 and $record->valor_parcela != $record->valor_pago) {
                            Notification::make()
                                ->title('RECEBIMENTO PARCIAL')
                                ->success()
                                ->body('Deseja lançar o valor restante de R$ ' . ($record->valor_parcela - $record->valor_pago) . ' como uma nova parcela?')
                                ->actions([
                                    Action::make('Sim')
                                        ->button()
                                        ->url(route('novaParcelaPagar', $record)),

                                ])
                                ->persistent()
                                ->send();
                        }

                        $addFluxoCaixa = [
                            'valor' => ($record->valor_pago * -1),
                            'tipo'  => 'DEBITO',
                            'obs'   => 'Pagamento da Compra nº: ' . $record->compra_id . '',
                        ];

                        FluxoCaixa::create($addFluxoCaixa);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContasPagars::route('/'),
        ];
    }
}
