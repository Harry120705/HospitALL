<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Funcionario extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'FUNCIONARIOS';

    protected $fillable = [
        'nome',
        'cpf',
        'cargo',
        'especialidade',
        'hospital_id',
        'credenciais',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }
}
