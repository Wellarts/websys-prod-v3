<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContasReceberResource\Pages;
use App\Filament\Resources\ContasReceberResource\RelationManagers;
use App\Models\Cliente;
use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;



class ContasReceberResource extends Resource
{
    protected static ?string $model = ContasReceber::class;

    protected static ?string $navigationIcon = 'heroicon-m-arrow-trending-up';

    protected static ?string $navigationLabel = 'Contas a Receber';

    protected static ?string $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make('4')
                    ->schema([
                        Forms\Components\Select::make('cliente_id')
                            ->columnSpan([
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                            ->label('Cliente')
                            ->options(Cliente::all()->pluck('nome', 'id')->toArray())
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('ordem_parcela')
                            ->label('Parcela Nº')
                            ->readOnly()
                            ->maxLength(10),
                        Forms\Components\TextInput::make('venda_id')
                            ->hidden()
                            ->required(),
                        Forms\Components\TextInput::make('parcelas')
                            ->required()
                            ->readOnly()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('data_vencimento')
                            ->label('Data do Vencimento')
                            ->displayFormat('d/m/Y')
                            ->required(),

                        Forms\Components\DatePicker::make('data_pagamento')
                            ->label('Data do Recebimento')
                            ->displayFormat('d/m/Y'),
                        Forms\Components\TextInput::make('valor_total')
                            ->numeric()
                            ->label('Valor Total')
                            ->readOnly()
                            ->required(),
                        Forms\Components\TextInput::make('valor_parcela')
                            ->numeric()
                            ->label('Valor da Parcela')
                            ->readOnly()
                            ->required(),
                        Forms\Components\TextInput::make('valor_recebido')
                            ->numeric()
                            ->label('Valor Recebido'),
                        Forms\Components\Textarea::make('obs')
                            ->columnSpan([
                                'xl' => 3,
                                '2xl' => 3,
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
                    ->summarize(Sum::make()->money('BRL')->label('Total Parcelas'))
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
                    ->summarize(Sum::make()->money('BRL')->label('Total Recebido'))
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
                Filter::make('Aberta')
                    ->query(fn(Builder $query): Builder => $query->where('status', false)),
                SelectFilter::make('cliente')->relationship('cliente', 'nome'),
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
                    ->after(function ($livewire, $record) {

                        if ($record->status = 1 and $record->valor_parcela != $record->valor_recebido) {
                            Notification::make()
                                ->title('RECEBIMENTO PARCIAL')
                                ->success()
                                ->body('Deseja lançar o valor restante de R$ ' . ($record->valor_parcela - $record->valor_recebido) . ' como uma nova parcela?')
                                ->actions([
                                    Action::make('Sim')
                                        ->button()
                                         ->url(route('novaParcela', $record)),
               
                                ])
                                ->persistent()
                                ->send();
                        }

                        $addFluxoCaixa = [
                            'valor' => ($record->valor_recebido),
                            'tipo'  => 'CREDITO',
                            'obs'   => 'Recebido da venda nº: '.$record->venda_id. '',
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
            'index' => Pages\ManageContasRecebers::route('/'),
        ];
    }

    
}
