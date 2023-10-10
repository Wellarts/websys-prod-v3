<?php

namespace App\Filament\Resources\ContasReceberPDVResource\Pages;

use App\Filament\Resources\ContasReceberPDVResource;
use App\Models\ContasReceber;
use App\Models\FluxoCaixa;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateContasReceberPDV extends CreateRecord
{
    protected static string $resource = ContasReceberPDVResource::class;

    public function after($data, $record) {
       
            if($record->parcelas > 1)
            {
                $valor_parcela = ($record->valor_total / $record->parcelas);
                $vencimentos = Carbon::create($record->data_vencimento);
                for($cont = 1; $cont < $data['parcelas']; $cont++)
                {
                                    $dataVencimentos = $vencimentos->addDays(30);
                                    $parcelas = [
                                    'venda_id' => $record->venda_id,
                                    'cliente_id' => $data['cliente_id'],
                                    'valor_total' => $data['valor_total'],
                                    'parcelas' => $data['parcelas'],
                                    'ordem_parcela' => $cont+1,
                                    'data_vencimento' => $dataVencimentos,
                                    'valor_recebido' => 0.00,
                                    'status' => 0,
                                    'obs' => $data['obs'],
                                    'valor_parcela' => $valor_parcela,
                                    ];
                        ContasReceber::create($parcelas);
                }

            }
            else
            {
                $addFluxoCaixa = [
                    'valor' => ($data['valor_total']),
                    'tipo'  => 'CREDITO',
                    'obs'   => 'Recebido da venda em PDV',
                ];

                
                FluxoCaixa::create($addFluxoCaixa);
            }

        }
    
    }

