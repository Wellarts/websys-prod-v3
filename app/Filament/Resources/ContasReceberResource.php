<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContasReceberResource\Pages;
use App\Filament\Resources\ContasReceberResource\RelationManagers;
use App\Models\Cliente;
use App\Models\ContasReceber;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContasReceberResource extends Resource
{
    protected static ?string $model = ContasReceber::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-trending-up';

    protected static ?string $navigationLabel = 'Contas a Receber';

    protected static ?string $navigationGroup = 'Financeiro';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fornecedor_id')
                ->label('Fornecedor')
                ->options(Cliente::all()->pluck('nome', 'id')->toArray())
                ->required()
                ->disabled(),
            Forms\Components\TextInput::make('compra_id')
                ->hidden()
                ->required(),
            Forms\Components\TextInput::make('parcelas')
                ->required()
                ->readOnly()
                ->maxLength(255),
            Forms\Components\TextInput::make('ordem_parcela')
                ->label('Parcela Nº')
                ->readOnly()
                ->maxLength(10),
            Forms\Components\DatePicker::make('data_vencimento')
                ->displayFormat('d/m/Y')
                ->required(),
            Forms\Components\TextInput::make('valor_total')
                ->readOnly()
                ->required(),
            Forms\Components\DatePicker::make('data_pagamento')
                ->label('Data do Recebimento')
                ->displayFormat('d/m/Y'),
            Forms\Components\Toggle::make('status')
            ->default('true')
            ->label('Recebido')
            ->required()
            ->live()
            ->afterStateUpdated(function (Get $get, Set $set) {
                         if($get('status') == 1)
                             {
                                 $set('valor_pago', $get('valor_parcela'));
                                 $set('data_pagamento', Carbon::now()->format('Y-m-d'));

                             }
                         else
                             {

                                 $set('valor_pago', 0);
                                 $set('data_pagamento', null);
                             }
                         }
             ),

            Forms\Components\TextInput::make('valor_parcela')
                ->readOnly()
                ->required(),
            Forms\Components\TextInput::make('valor_pago'),
            Forms\Components\Textarea::make('obs'),
        ]);
            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('cliente.nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ordem_parcela')
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
                    ->label('Pago')
                    ->boolean(),
                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Data do Recebimento')
                    ->badge()
                    ->color('success')
                    ->date(),    
                Tables\Columns\TextColumn::make('valor_pago')
                    ->label('Recebido')
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageContasRecebers::route('/'),
        ];
    }    
}
