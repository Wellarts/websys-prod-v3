<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Produto extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
            'nome',
            'estoque',
            'valor_compra',
            'lucratividade',
            'valor_venda',
            'total_compra',
            'total_venda',
            'total_lucratividade'
    ];

    public function ProdutoFornecedor() 
    {
        return $this->hasMany(ProdutoFornecedor::class);
    }

    public function itensCompra() 
    {
        return $this->hasMany(ItensCompra::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
