<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaccion extends Model
{
    use HasFactory;

    protected $table = 'tbl_transacciones';
    protected $primaryKey = 'id_transaccion';

    protected $fillable = [
        'nombre',
        'apellido',
        'pais',
        'direccion',
        'nit',
        'descripcion',
        'moneda',
        'cantidad',
        'estado'
    ];
    public $timestamps = false;
}