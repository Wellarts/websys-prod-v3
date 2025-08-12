<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VwTotalVendasPorClienteResource\Pages;
use App\Models\VwTotalVendasPorCliente;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class VwTotalVendasPorClienteResource extends Resource
{
    protected static ?string $model = VwTotalVendasPorCliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Vendas por Cliente';

    protected static ?string $navigationGroup = 'Consultas';

    protected static ?int $navigationSort = 20;

    

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('valor_total',
             'desc')
            ->columns([
                Tables\Columns\TextColumn::make('cliente_nome')
                    ->label('Cliente')
                    ->sortable()                    
                    ->searchable(),
                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Valor Total')
                    ->money('BRL')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('ultima_compra')
                    ->label('Última Compra')
                    ->color(fn ($record) => $record->ultima_compra == 'Nunca comprou' ? 'danger' : 'success')              
                    ->sortable(),                    
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
                SelectFilter::make('cliente')
                    ->relationship('cliente', 'nome')
                    ->label('Cliente')
                    ->searchable()
                    ->multiple(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ManageVwTotalVendasPorClientes::route('/'),
        ];
    }
}
