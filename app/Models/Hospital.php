<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Hospital extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'hospitais';

    protected $fillable = [
        'nome',
        'endereco',
        'setores',
    ];
}
