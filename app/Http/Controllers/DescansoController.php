<?php

namespace App\Http\Controllers;

use App\Models\Descanso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DescansoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function getDescansos()
{
    $descansos = DB::table('descansos')->pluck('fecha');
    return response()->json($descansos);
}


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fechas' => 'required|array|min:1',
            'fechas.*' => 'date_format:Y-m-d',
        ]);

        // Almacenar los días de descanso en la base de datos
        foreach ($request->fechas as $fecha) {
            Descanso::create([
                'fecha' => $fecha,
            ]);
        }

        return response()->json(['message' => 'Días de descanso guardados correctamente.'], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Descanso $descanso)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Descanso $descanso)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Descanso $descanso)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Descanso $descanso)
    {
        //
    }
}
