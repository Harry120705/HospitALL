<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Paciente extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'PACIENTES';

    protected $fillable = [
        'nome',
        'data_nascimento',
        'dados_clinicos_fixos',
        'contato',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];
}
