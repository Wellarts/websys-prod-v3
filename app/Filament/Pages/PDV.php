<?php

namespace App\Filament\Pages;

use App\Models\Cliente;
use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use App\Models\FormaPgmto;
use App\Models\Funcionario;
use App\Models\Produto;
use App\Models\PDV as PDVs;
use App\Models\Venda;
use App\Models\VendaPDV;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextInputColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\DeleteAction;



class PDV extends  page implements HasForms, HasTable
{

    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.p-d-v';

    protected static ?string $title = 'PDV Express';

    protected static ?string $navigationGroup = 'Ponto de Venda';

    public ?array $data = [];

    public $produto_id;
    public $qtd;
    public $pdv;
    public $venda;
    

    public function mount(): void
    {
        $this->form->fill();
        $this->venda =  random_int(0000000000, 9999999999);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Ponto de Venda')
                    ->columns(4)
                    ->schema([
                        TextInput::make('produto_id')
                            ->numeric()
                            ->label('Produto')
                            ->autocomplete()
                            ->autofocus()
                            ->extraInputAttributes(['tabindex' => 1])
                            ->live(debounce: 300)
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {

                                $this->updated($state, $state);
                            }),

                    ]),
            ]);
    }

    public function updated($name, $value): void
    {

        if ($name === 'produto_id') {



            $produto = Produto::where('codbar', '=', $value)->first();

            if ($produto) {
                $addProduto = [
                    'produto_id' => $produto->id,
                    'venda_p_d_v_id' => $this->venda,
                    'valor_venda' => $produto->valor_venda,
                    'pdv_id' => '',
                    'acres_desc' => 0,
                    'qtd' => 1,
                    'sub_total' => $produto->valor_venda * 1,
                    'valor_custo_atual' => $produto->valor_compra,
                ];

                PDVs::create($addProduto);
                $this->produto_id = '';
                $this->qtd = '';
            } elseif ($produto == null) {
                Notification::make()
                    ->title('Produto não cadastrado')
                    ->warning()
                    ->send();
            }
        }
    }

    protected function getTableQuery(): Builder
    {

        return PDVs::query()->where('venda_p_d_v_id', $this->venda);
    }

    protected function getTableColumns(): array
    {
        return [

            TextColumn::make('produto.nome'),
            TextInputColumn::make('qtd')
                ->updateStateUsing(function (Model $record, $state) {
                    $record->sub_total = ($state * $record->valor_venda);
                    $record->qtd = $state;
                    $record->save();
                })

                ->label('Quantidade'),
            TextColumn::make('valor_venda')
                ->label('Valor Unitário')
                ->money('BRL'),
            TextInputColumn::make('acres_desc')
                ->label('Acréscimo/Desconto')
                ->updateStateUsing(function (Model $record, $state) {
                    $record->sub_total = (($record->valor_venda + $state) * $record->qtd);
                    $record->acres_desc = $state;
                    $record->save();
                })
                ->label('Acres/Desc'),
            TextColumn::make('sub_total')
                ->label('Sub-Total')
                ->money('BRL')
                ->summarize(Sum::make()->label('TOTAL')->money('BRL')),
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Finalizar Venda (F7)')
                ->model(VendaPDV::class)
                ->createAnother(false)
                ->successNotificationTitle('Venda em PDV finalizada com sucesso!')
                ->keyBindings(['keypress', 'f7'])
                ->form([
                    Grid::make('4')
                        ->schema([
                            TextInput::make('id')
                                ->label('Código da Venda')
                                ->readOnly()
                                ->default($this->venda),
                            Select::make('cliente_id')
                                ->label('Cliente')
                                ->default('1')
                                ->options(Cliente::all()->pluck('nome', 'id')->toArray()),
                            Select::make('funcionario_id')
                                ->label('Vendedor')
                                ->default('1')
                                ->options(Funcionario::all()->pluck('nome', 'id')->toArray()),
                            Select::make('formaPgmto_id')
                                ->label('Forma de Pagamento')
                                ->default('1')
                                ->native(false)
                                ->searchable()
                                ->options(FormaPgmto::all()->pluck('nome', 'id')->toArray()),
                            DatePicker::make('data_venda')
                                ->label('Data da Venda')
                                ->default(now()),
                            TextInput::make('valor_total')
                                ->label('Valor Total')
                                ->readOnly()
                                ->default(function () {
                                    $valorTotal = PDVs::where('venda_p_d_v_id', $this->venda)->sum('sub_total');
                                    return $valorTotal;
                                }),
                            TextInput::make('valor_pago')
                                ->label('Valor Pago')
                                ->autofocus()
                                ->extraInputAttributes(['tabindex' => 1])
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, $state, $get) {
                                    $set('troco', ($state - $get('valor_total')));
                                })
                                ->autofocus(),
                            TextInput::make('troco')
                                ->disabled()
                                ->inputMode('decimal')
                                ->label('Troco'),
                            Radio::make('financeiro')
                                ->label('Lançamento Financeiro')
                                ->live()
                                ->options([
                                    '1' => 'Direto no Caixa',
                                    '2' => 'Conta a Receber'
                                ])->default('1'),
                            TextInput::make('parcelas')
                                ->numeric()
                                ->required()
                                ->label('Qtd de Parcelas')
                                ->hidden(fn (Get $get): bool => $get('financeiro') != '2')


                        ])

                ])
                ->after(function () {

                    $itensPDV = PDVs::where('venda_p_d_v_id', $this->venda)->get();

                    foreach ($itensPDV as $itens) {
                        $updProduto = Produto::find($itens->produto_id);
                        $updProduto->estoque -= $itens->qtd;
                        $updProduto->save();
                    }
                })->successRedirectUrl(function ($data, $record) {
                    //   dd($data);
                    if ($data['financeiro'] == 1) {

                        $addFluxoCaixa = [
                            'valor' => ($data['valor_total']),
                            'tipo'  => 'CREDITO',
                            'obs'   => 'Recebido da venda nº: ' .$this->venda. '',
                        ];

                        FluxoCaixa::create($addFluxoCaixa);
                        return route('filament.admin.pages.p-d-v');

                    } 
                    else {
                        $valor_parcela = ($record->valor_total / $data['parcelas']);
                        $vencimentos = Carbon::now();
                        for($cont = 0; $cont < $data['parcelas']; $cont++)
                        {
                                            $dataVencimentos = $vencimentos->addDays(30);
                                            $parcelas = [
                                            'vendapdv_id' => $this->venda,
                                            'cliente_id' => $data['cliente_id'],
                                            'valor_total' => $data['valor_total'],
                                            'parcelas' => $data['parcelas'],
                                            'ordem_parcela' => $cont+1,
                                            'data_vencimento' => $dataVencimentos,
                                            'valor_recebido' => 0.00,
                                            'status' => 0,
                                            'obs' => 'Venda em PDV - Nº '.$this->venda,
                                            'valor_parcela' => $valor_parcela,
                                            ];
                                ContasReceber::create($parcelas);
                        }      

                         return route('filament.admin.pages.p-d-v');
                    }

                    //  return route('filament.admin.pages.p-d-v'); 

                })


        ];
    }

    protected function getTableActions(): array
    {
        return [

            DeleteAction::make('Excluir'),

        ];
    }
}
