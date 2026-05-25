<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Paciente extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'PACIENTES';

    protected $fillable = [
        'nome',
        'cpf',
        'data_nascimento',
        'genero',
        'cartao_sus',
        'telefone',
        'status_paciente',
        'setor_id',
        'setor_nome',
        'dados_clinicos_fixos',
        'contato',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function getContatoAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }

    public function getDadosClinicosFixosAttribute($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }
        return $value ?? [];
    }
}
