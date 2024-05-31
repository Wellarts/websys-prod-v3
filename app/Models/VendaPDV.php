<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VendaPDV extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'id',
        'cliente_id',
        'itens_pdv_id',
        'funcionario_id',
        'data_venda',
        'forma_pgmto_id',
        'valor_total',
        'lucro_venda',
        'obs',

    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }

    public function formaPgmto()
    {
        return $this->belongsTo(FormaPgmto::class);
    }

    public function itensVenda()
    {
        return $this->hasMany(PDV::class);
    }

    public function contasReceber()
    {
        return $this->hasMany(ContasReceber::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }

    public function pdv()
    {
        return $this->hasMany(PDV::class);
    }

}
