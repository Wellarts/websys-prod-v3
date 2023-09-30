<?php

namespace App\Models;

use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class Venda extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cliente_id',
        'funcionario_id',
        'data_venda',
        'formaPgmto_id',
        'valor_total',
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
        return $this->belongsTo(FormaPgmto::class, 'formaPgmto_id', 'id');
    }

    public function itensVenda()
    {
        return $this->hasMany(ItensVenda::class);
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


}
