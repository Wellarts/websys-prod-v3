<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContasReceberPDVResource\Pages;
use App\Filament\Resources\ContasReceberPDVResource\RelationManagers;
use App\Models\Cliente;
use App\Models\ContasReceber;
use App\Models\PDV;
use App\Models\VendaPDV;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContasReceberPDVResource extends Resource
{
    protected static ?string $model = ContasReceber::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public function mount() {
        dd('teste');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Contas a Receber - PDV')
                ->columns([
                    'xl' => 4,
                    '2xl' => 4,
                ])
                    ->schema([
                        Forms\Components\TextInput::make('venda_id')
                            
                            ->hidden()
                            ->required(),
                        Forms\Components\Select::make('cliente_id')
                            ->label('Cliente')
                            ->native(false)
                            ->options(Cliente::all()->pluck('nome', 'id')->toArray())
                            ->required(),
                        Forms\Components\TextInput::make('valor_total')
                            ->default(function($data){
                                $pdv = VendaPDV::find($data['id']);
                                return  $pdv->valor_total;
                            })
                            ->label('Valor Total')
                            ->readOnly()
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
                            ->label('Data do Vencimento')
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Forms\Components\TextInput::make('valor_parcela')
                            ->label('Valor da Parcela')
                            ->readOnly()
                            ->required(),
                        Forms\Components\DatePicker::make('data_pagamento')
                            ->label('Data do Recebimento')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('valor_recebido')
                            ->label('Valor Recebido'),
                                          
                        
                        Forms\Components\Textarea::make('obs')
                            ->columnSpan([
                                'xl' => 4,
                                '2xl' => 4,
                            ])
                            ->label('Observações'),
                        Forms\Components\Toggle::make('status')
                            ->default('true')
                            ->label('Recebido')
                            ->required()
                            ->live()
                            ->afterStateUpdated(
                                function (Get $get, Set $set) {
                                    if ($get('status') == 1) {
                                        $set('valor_recebido', $get('valor_parcela'));
                                        $set('data_pagamento', Carbon::now()->format('Y-m-d'));
                                    } else {

                                        $set('valor_recebido', 0);
                                        $set('data_pagamento', null);
                                    }
                                }
                            ),

                    ])

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
                    ->alignCenter()
                    ->label('Parcela Nº'),
                Tables\Columns\TextColumn::make('valor_total')
                    ->badge()
                    ->color('warning')
                    ->label('Valor Total')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('data_vencimento')
                    ->alignCenter()
                    ->label('Data do Vencimento')
                    ->badge()
                    ->color('danger')
                    ->sortable()
                    ->date(),

                Tables\Columns\TextColumn::make('valor_parcela')
                    ->alignCenter()
                    ->badge()
                    ->color('danger')
                    ->label('Valor da Parcela')
                    ->money('BRL'),
                Tables\Columns\IconColumn::make('status')
                    ->alignCenter()
                    ->label('Recebido')
                    ->boolean(),
                Tables\Columns\TextColumn::make('data_pagamento')
                    ->alignCenter()
                    ->label('Data do Recebimento')
                    ->badge()
                    ->color('success')
                    ->date(),
                Tables\Columns\TextColumn::make('valor_recebido')
                    ->alignCenter()
                    ->label('Valor Recebido')
                    ->badge()
                    ->color('success'),
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContasReceberPDVS::route('/'),
            'create' => Pages\CreateContasReceberPDV::route('/create'),
            'edit' => Pages\EditContasReceberPDV::route('/{record}/edit'),
        ];
    }
}
