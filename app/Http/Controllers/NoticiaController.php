<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoticiaController extends Controller
{
    // Mostrar las noticias en el foro del admin
    public function index()
    {
        // Obtener todas las noticias junto con la información del usuario que las publicó
        $noticias = Noticia::with('usuario')->get();

        return Inertia::render('ForoAdmin', [
            'noticias' => $noticias
        ]);
    }

    // Crear una nueva noticia
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
        ]);

        // Crear la noticia en la base de datos
        Noticia::create([
            'usuario_id' => auth()->id(),
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
        ]);

        // Redirigir al foro con un mensaje de éxito
        return redirect()->route('admin-foro')->with('message', 'Noticia publicada con éxito.');
    }

    // Mostrar las noticias al cliente en la página de "Reservar Cita"
    public function showNoticias()
    {
        // Obtener todas las noticias para los clientes
        $noticias = Noticia::with('usuario')->get();

        return Inertia::render('ReservarCitaCliente', [
            'noticias' => $noticias,
        ]);
    }

    // Editar una noticia
    public function edit(Noticia $noticia)
    {
        // Devolver la vista con la noticia a editar (si usas una vista separada para editar)
        return Inertia::render('EditarNoticia', [
            'noticia' => $noticia
        ]);
    }

    // Actualizar una noticia
    public function update(Request $request, Noticia $noticia)
    {
        // Validar los datos actualizados
        $request->validate([
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
        ]);

        // Actualizar la noticia con los nuevos datos
        $noticia->update([
            'titulo' => $request->titulo,
            'contenido' => $request->contenido,
        ]);

        // Redirigir al foro con un mensaje de éxito
        return redirect()->route('admin-foro')->with('message', 'Noticia actualizada con éxito.');
    }

    // Eliminar una noticia
    public function destroy(Noticia $noticia)
{
    // Eliminar la noticia de la base de datos
    $noticia->delete();

    // Redirigir al foro con un mensaje de éxito
    return redirect()->route('admin-foro')->with('message', 'Noticia eliminada con éxito.');
}

}
