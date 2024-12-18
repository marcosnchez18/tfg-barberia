<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CandidaturaController;
use App\Http\Controllers\NoticiaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\DesacansoController;
use App\Http\Controllers\DescansoController;
use App\Http\Controllers\FichaClienteController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\OfertaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\RecompensaController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\ServicioUsuarioController;
use App\Models\Proveedor;
use App\Models\ServicioUsuario;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta para la página principal de bienvenida
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

// Rutas de autenticación
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.authenticate');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Página para cuentas inactivas
Route::get('/account-inactive', function () {
    return Inertia::render('AccountInactive', [
        'message' => 'UPS !! Tu cuenta está desactivada por conductas inapropiadas. Si desea recuperarla, contacte con nosotros.',
    ]);
})->name('account.inactive');

// Registro de usuario
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

// Rutas para verificación de email
Route::get('/email/verify', function () {
    return Inertia::render('Auth/VerifyEmail');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('mi-cuenta');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'El enlace de verificación ha sido reenviado.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Rutas protegidas para dashboards y datos de clientes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard del cliente
    Route::get('/mi-cuenta', function () {
        if (auth()->user()->rol === 'admin') {
            return redirect()->route('mi-gestion-admin');
        }
        return Inertia::render('Dashboard', ['user' => auth()->user()]);
    })->name('mi-cuenta');

    // Dashboard del administrador
    Route::get('/mi-gestion-admin', [AdminController::class, 'dashboard'])->name('mi-gestion-admin');

    Route::get('/opciones', function () {
        return Inertia::render('Opciones');
    })->name('opciones');

    // Ruta para la vista de citas del barbero en la interfaz de administración
    Route::get('/admin/citas', function () {
        return Inertia::render('CitasAdmin');
    })->name('admin-citas');

    // Ruta para obtener las citas del barbero logueado
    Route::get('/admin/citas-barbero', [AdminController::class, 'citasBarbero'])->name('admin.citas-barbero');
    Route::get('/admin/citas-barberia', [AdminController::class, 'citasBarberia'])->name('admin.citas-barberia');

    // Foro de noticias del administrador
    Route::get('/admin/foro', [NoticiaController::class, 'index'])->name('admin-foro');
    Route::post('/admin/foro', [NoticiaController::class, 'store'])->name('noticias.store');
    Route::put('/admin/foro/{noticia}', [NoticiaController::class, 'update'])->name('noticias.update');
    Route::delete('/admin/foro/{noticia}', [NoticiaController::class, 'destroy'])->name('noticias.destroy');

    // Gestión de clientes para el administrador
    Route::get('/admin/clientes', [ClienteController::class, 'index'])->name('admin-clientes');
    Route::delete('/admin/clientes/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::patch('/admin/clientes/{id}/deshabilitar', [ClienteController::class, 'deshabilitar'])->name('clientes.deshabilitar');
    Route::patch('/admin/clientes/{id}/habilitar', [ClienteController::class, 'habilitar'])->name('clientes.habilitar');

    Route::get('/empleo', function () {
        return inertia('Empleo');
    })->name('empleo');
    Route::get('/empleo', [OfertaController::class, 'index'])->name('empleo');

    Route::resource('ofertas', OfertaController::class);

    Route::get('/citas-barberia', function () {
        return Inertia::render('CitasBarberia');
    })->name('citas-barberia');

    Route::get('/admin/proveedores/nuevo', function () {
        return Inertia::render('NuevoProveedor');
    })->name('admin.proveedores.nuevo');

    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');




    Route::patch('/admin/citas/{id}/cambiar-estado', [AdminController::class, 'cambiarEstado'])->name('citas.cambiar-estado');
    Route::delete('/admin/citas/{id}/cancelar', [AdminController::class, 'cancelarCita'])->name('citas.cancelar');
    Route::get('/admin/citas-barbero', [CitaController::class, 'citasDelDia']);

    Route::get('/admin/citas/{fecha}', [AdminController::class, 'citasPorDia']);
    Route::patch('/admin/citas/{id}/cambiar-estado', [AdminController::class, 'cambiarEstado']);
    Route::delete('/admin/citas/{id}/cancelar', [AdminController::class, 'cancelarCita']);
    Route::patch('/admin/citas/{id}/cambiar-metodo-pago', [AdminController::class, 'cambiarMetodoPago']);


    Route::get('/admin/nuevos-servicios', [ServicioController::class, 'create'])->name('admin.servicios.create');
    Route::post('/admin/servicios', [ServicioController::class, 'store'])->name('admin.servicios.store');

    Route::get('/admin/servicios/editar', [ServicioController::class, 'edit'])->name('admin.servicios.editar');

    Route::patch('/admin/servicios/{id}', [ServicioController::class, 'update'])->name('servicios.update');


    Route::delete('/admin/servicios/{id}', [ServicioController::class, 'destroy'])->name('servicios.destroy');

    Route::get('/admin/barberos/create', [AdminController::class, 'createBarbero'])->name('admin.barberos.create');
    Route::post('/admin/barberos/store', [AdminController::class, 'store'])->name('admin.barberos.store');
    Route::get('/admin/barberos/editar', [AdminController::class, 'editarBarberos'])->name('admin.barberos.editar');
    Route::get('/admin/proveedores/editar', [ProveedorController::class, 'editarProveedores'])->name('admin.proveedores.editar');

    Route::get('/mis-datos-admin', function () {
        return Inertia::render('MisDatosAdmin');
    })->name('mis-datos-admin');

    Route::get('/mis-datos-admin', [AdminController::class, 'misDatos'])->name('mis-datos-admin');

    // Ruta para actualizar datos del administrador
    Route::patch('/admin/actualizar-datos', [AdminController::class, 'actualizarDatos'])->name('admin.actualizar-datos');
    Route::post('/admin/actualizar-foto/{id}', [AdminController::class, 'actualizarFoto'])->name('admin.actualizar-foto');


    Route::get('/admin/asignar-servicios', function () {
        return Inertia::render('Asignar');
    })->name('admin.asignar.servicios');

    Route::post('/admin/asignar-servicios', [ServicioUsuarioController::class, 'asignarServicios'])
        ->name('admin.asignar-servicios');

    Route::get('/api/barberos/{barbero}/servicios', [ServicioUsuarioController::class, 'getServiciosAsignados']);
    Route::post('/admin/desasignar-servicio', [ServicioUsuarioController::class, 'desasignarServicio']);

    Route::post('/admin/descansos', [DescansoController::class, 'store'])->name('admin.descansos.store');

    // routes/web.php o routes/api.php
    Route::post('/admin/dias-descanso', [AdminController::class, 'guardarDiasDescanso'])->name('admin.diasDescanso');
    Route::get('/descansos', [DescansoController::class, 'getDescansos']);





    Route::delete('/trabajadores/{id}', [AdminController::class, 'destroy'])->name('trabajadores.destroy');

    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

    Route::patch('/trabajadores/{id}/habilitar', [AdminController::class, 'habilitar'])->name('trabajadores.habilitar');
    Route::patch('/trabajadores/{id}/deshabilitar', [AdminController::class, 'deshabilitar'])->name('trabajadores.deshabilitar');

    Route::patch('/trabajadores/{id}/updateField', [AdminController::class, 'updateField'])->name('trabajadores.updateField');
    Route::post('/trabajadores/{id}/updatePhoto', [AdminController::class, 'updatePhoto'])->name('trabajadores.updatePhoto');

    Route::get('/candidatos/{oferta}', [CandidaturaController::class, 'verCandidatos'])->name('candidatos.index');
    Route::patch('/candidaturas/{id}/cambiar-estado', [CandidaturaController::class, 'cambiarEstado'])->name('candidaturas.cambiarEstado');





    // Rutas para el cliente (incluyendo edición de datos)
    Route::get('/reservar-cita', [NoticiaController::class, 'showNoticias'])->name('reservar-cita');
    Route::get('/mis-citas', [CitaController::class, 'misCitas'])->name('mis-citas');

    Route::get('/mis-citas/elegir', function () {
        return Inertia::render('ElegirCita');
    })->name('mis-citas-elegir');

    // Rutas para disponibilidad y reserva de citas
    Route::get('/api/citas/disponibilidad', [CitaController::class, 'disponibilidad'])->name('citas.disponibilidad');
    Route::get('/api/citas/horas-reservadas', [CitaController::class, 'horasReservadas']);
    Route::get('/api/servicios', [ServicioController::class, 'index'])->name('servicios.index');
    Route::get('/api/barberos/{barberoId}/servicios', [AdminController::class, 'getServiciosPorBarbero'])
        ->name('barberos.servicios');
    Route::get('/api/barberos', [AdminController::class, 'obtenerBarberos']);
    Route::get('/api/candidaturas', [CandidaturaController::class, 'obtenerMisCandidaturas'])->name('candidaturas.obtener');

    Route::post('/citas/reservar', [CitaController::class, 'reservar'])->name('citas.reservar');
    Route::get('/admin/user/saldo', [AdminController::class, 'getSaldo'])->name('admin.user.saldo');

    Route::patch('/admin/user/quitar-saldo', [AdminController::class, 'quitaSaldo'])->name('admin.user.quitar-saldo');

    // Nueva ruta para actualizar el método de pago de la cita
    Route::patch('/citas/{id}/actualizar-metodo-pago', [CitaController::class, 'actualizarMetodoPago'])->name('citas.actualizar-metodo-pago');

    // Ruta para cancelar una cita y mostrar el mensaje de devolución de importe
    Route::delete('/citas/{id}/cancelar', [CitaController::class, 'cancelar'])->name('citas.cancelar');
    Route::patch('/citas/{id}/modificar', [CitaController::class, 'modificar'])->name('citas.modificar');



    Route::patch('/citas/{id}/calificar', [CitaController::class, 'calificar'])->name('citas.calificar');


    // Mostrar y actualizar datos del cliente en la ruta /mis-datos
    Route::get('/mis-datos', [ClienteController::class, 'edit'])->name('mis-datos');
    Route::get('/mi-ficha', [FichaController::class, 'show'])->name('mi-ficha');
    Route::patch('/mis-datos', [ClienteController::class, 'update'])->name('cliente.update');
    Route::put('/clientes/ficha/{id}', [FichaController::class, 'update'])->name('clientes.ficha.update');
    Route::post('/cliente/eliminar', [ClienteController::class, 'eliminarCuenta'])->name('cliente.eliminar');
    Route::patch('/cliente/actualizar-datos', [ClienteController::class, 'actualizarDatos'])->name('cliente.actualizar');
    Route::get('/clientes/{id}/ficha', [ClienteController::class, 'mostrarFicha'])->name('clientes.ficha');
    Route::post('/clientes/{id}/upload-image', [ClienteController::class, 'uploadImage'])->name('clientes.ficha.uploadImage');

    Route::get('/miempleo', function () {
        return Inertia::render('MiEmpleo');
    })->name('miempleo');


    Route::get('/trabcli', [OfertaController::class, 'trabaja2'])->name('trabcli');
    Route::get('/inscribirsecliente/{id}', [OfertaController::class, 'inscribirse2'])->name('inscribirsecliente');
    Route::post('/guardar-candidatura-user', [CandidaturaController::class, 'guardarCandidaturalog']);
    Route::get('/usuario-actual', function (Request $request) {

        if (Auth::check()) {
            return response()->json(Auth::user());
        }
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    });
});

// Rutas para restablecimiento de contraseña
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


Route::get('/invitado', function () {
    return Inertia::render('Invitado');
})->name('invitado');

// Rutas adicionales (públicas)
Route::get('/sobre-nosotros', function () {
    return Inertia::render('PaginaSobreNos');
})->name('sobre-nosotros');

Route::get('/servicios', function () {
    return Inertia::render('Servi');
})->name('servicios');

Route::get('/contacto', function () {
    return Inertia::render('Contacto');
})->name('contacto');

Route::get('/equipo', function () {
    return Inertia::render('Equipo');
})->name('equipo');

// Páginas personales de Daniel y José
Route::get('/daniel', function () {
    return Inertia::render('Daniel');
})->name('daniel');

Route::get('/jose', function () {
    return Inertia::render('Jose');
})->name('jose');

Route::get('/trabaja-nosotros', [OfertaController::class, 'trabaja'])->name('trabajaNosotros');
Route::get('/inscribirse/{id}', [OfertaController::class, 'inscribirse'])->name('inscribirse');


Route::post('/verificar-cliente', [CandidaturaController::class, 'verificarCliente']);
Route::post('/guardar-candidatura', [CandidaturaController::class, 'guardarCandidatura']);
Route::post('/consultar-estado', [CandidaturaController::class, 'consultarEstado']);

Route::get('/servicios-invitado', function () {
    return Inertia::render('Huecos');
})->name('servicios-invitado');


Route::get('/api/public/servicios', [ServicioController::class, 'index'])->name('servicios.public.index');

Route::get('/api/public/citas/disponibilidad', [RecompensaController::class, 'disponibilidad'])->name('public.citas.disponibilidad');


Route::get('/api/public/barberos/{barberoId}/servicios', [RecompensaController::class, 'getServiciosPorBarbero'])
    ->name('public.barberos.servicios');


Route::get('/api/public/barberos', [AdminController::class, 'obtenerBarberos']);
