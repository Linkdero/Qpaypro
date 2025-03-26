<?php

namespace App\Http\Controllers;

use App\Models\Transaccion;
use Illuminate\Http\Request;

class TransaccionController extends Controller
{
    public function registrarTransaccion(Request $request)
    {
        // Validación básica
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'pais' => 'required|string|max:50',
            'direccion' => 'required|string',
            'nit' => 'nullable|string|max:20',
            'descripcion' => 'required|string',
            'moneda' => 'required|string|size:3',
            'cantidad' => 'required|numeric',
            'estado' => 'required|boolean'
        ]);

        $transaccion = Transaccion::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'pais' => $request->pais,
            'direccion' => $request->direccion,
            'nit' => $request->nit ?? 'C/F',
            'descripcion' => $request->descripcion,
            'moneda' => $request->moneda,
            'cantidad' => $request->cantidad,
            'estado' => $request->estado
        ]);

        return response()->json([
            'success' => true,
            'id_transaccion' => $transaccion->id_transaccion
        ]);
    }
}
