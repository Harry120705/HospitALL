<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Atendimento extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'atendimentos';

    protected $fillable = [
        'paciente_id',
        'hospital_id',
        'status',
        'dados_iniciais',
        'evolucoes_medicas',
        'administracao_enfermagem',
        'alocacao_leito',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }
}
