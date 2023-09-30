<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use App\Filament\Resources\ProdutoResource\Pages;
use App\Filament\Resources\ProdutoResource\RelationManagers;
use App\Filament\Resources\ProdutoResource\RelationManagers\ProdutoFornecedorRelationManager;
use App\Models\Produto;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;

    protected static ?string $navigationIcon = 'heroicon-s-shopping-bag';

    protected static ?string $navigationGroup = 'Cadastros';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Cadastro')
                    ->columns([
                        'xl' => 3,
                        '2xl' => 3,
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->columnSpan([
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('estoque'),
                        Forms\Components\TextInput::make('valor_compra')
                            ->live(onBlur:true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set('valor_venda', ((((float)$get('valor_compra') * (float)$get('lucratividade'))/100) + (float)$get('valor_compra')));
                            }),
                        Forms\Components\TextInput::make('lucratividade')
                           // ->required()
                            ->live(onBlur:true)
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $set('valor_venda', ((((float)$get('valor_compra') * (float)$get('lucratividade'))/100) + (float)$get('valor_compra')));
                            }),
                        Forms\Components\TextInput::make('valor_venda')
                           // ->disabled(),
                           ->live(onBlur:true)
                           ->afterStateUpdated(function (Get $get, Set $set) {
                            $set('lucratividade', (((((float)$get('valor_venda') - (float)$get('valor_compra')) / (float)$get('valor_compra')) * 100)));
                        }),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('estoque')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_compra')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('lucratividade')
                    ->label('Lucratividade (%)'),
                Tables\Columns\TextColumn::make('valor_venda')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                ExportBulkAction::make(),
               


            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProdutoFornecedorRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/create'),
            'edit' => Pages\EditProduto::route('/{record}/edit'),

        ];
    }
}
