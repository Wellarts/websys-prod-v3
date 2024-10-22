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
            'codbar',
            'estoque',
            'valor_compra',
            'lucratividade',
            'valor_venda',
            'total_compra',
            'total_venda',
            'total_lucratividade',
            'foto',
            'tipo',
    ];

    protected $casts = [
        'foto' => 'array',
    ];

    public function ProdutoFornecedor() 
    {
        return $this->hasMany(ProdutoFornecedor::class);
    }

    public function itensCompra() 
    {
        return $this->hasMany(ItensCompra::class);
    }

    public function itensVenda() 
    {
        return $this->hasMany(ItensVenda::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
