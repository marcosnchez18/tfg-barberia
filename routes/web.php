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
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoProveedorController;
use App\Http\Controllers\ProductoController;
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



















Route::middleware(['auth', 'verified', 'cliente'])->group(function () {
    // RUTAS DE CLIENTES

    Route::get('/barbershop', function () {
        return Inertia::render('Cliente/Shop');
    })->name('barbershop');



    // Rutas para el cliente (incluyendo edición de datos)
    Route::get('/reservar-cita', [NoticiaController::class, 'showNoticias'])->name('reservar-cita');
    Route::get('/mis-citas', [CitaController::class, 'misCitas'])->name('mis-citas');

    Route::get('/mis-citas/elegir', function () {
        return Inertia::render('Cliente/ElegirCita');
    })->name('mis-citas-elegir');

    Route::get('/api/descansos', [DescansoController::class, 'getDescansos']);

    // Rutas para disponibilidad y reserva de citas
    Route::get('/api/citas/disponibilidad', [CitaController::class, 'disponibilidad'])->name('citas.disponibilidad');
    Route::get('/api/citas/horas-reservadas', [CitaController::class, 'horasReservadas']);

    Route::get('/api/candidaturas', [CandidaturaController::class, 'obtenerMisCandidaturas'])->name('candidaturas.obtener');

    Route::get('/api/descansos/{barberoId}', [DescansoController::class, 'obtenerDiasDescanso']);

    Route::post('/citas/reservar', [CitaController::class, 'reservar'])->name('citas.reservar');

    Route::patch('/citas/{id}/calificar', [CitaController::class, 'calificar'])->name('citas.calificar');

    Route::patch('/citas/{id}/actualizar-metodo-pago', [CitaController::class, 'actualizarMetodoPago'])->name('citas.actualizar-metodo-pago');



    // Ruta para cancelar una cita y mostrar el mensaje de devolución de importe
    Route::delete('/citas/{id}/cancelar', [CitaController::class, 'cancelar'])->name('citas.cancelar');
    Route::patch('/citas/{id}/modificar', [CitaController::class, 'modificar'])->name('citas.modificar');

    // Mostrar y actualizar datos del cliente en la ruta /mis-datos
    Route::get('/mis-datos', [ClienteController::class, 'edit'])->name('mis-datos');
    Route::get('/mi-ficha', [FichaController::class, 'show'])->name('mi-ficha');


    Route::patch('/mis-datos', [ClienteController::class, 'update'])->name('cliente.update');

    Route::put('/clientes/ficha/{id}', [FichaController::class, 'update'])->name('clientes.ficha.update');
    Route::post('/cliente/eliminar', [ClienteController::class, 'eliminarCuenta'])->name('cliente.eliminar');
    Route::patch('/cliente/actualizar-datos', [ClienteController::class, 'actualizarDatos'])->name('cliente.actualizar');
    Route::post('/clientes/{id}/upload-image', [ClienteController::class, 'uploadImage'])->name('clientes.ficha.uploadImage');

    Route::get('/miempleo', function () {
        return Inertia::render('Cliente/MiEmpleo');
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

    Route::get('/api/barberos/{barbero}/servicios', [ServicioUsuarioController::class, 'getServiciosAsignados']);

    Route::get('/admin/user/saldo', [AdminController::class, 'getSaldo'])->name('admin.user.saldo');

    Route::patch('/admin/user/quitar-saldo', [AdminController::class, 'quitaSaldo'])->name('admin.user.quitar-saldo');

    Route::delete('/api/candidaturas/{localizador}', [CandidaturaController::class, 'destroy'])->name('candidaturas.destroy');



    Route::post('/guardar-carrito', function (Request $request) {
        session(['carrito' => $request->input('carrito')]); // Guarda el carrito en sesión
        return response()->json(['message' => 'Carrito guardado']);
    });

    Route::get('/tramitar-pedido', function () {
        return Inertia::render('Cliente/TramitarPedido', [
            'carrito' => session('carrito', []) // Pasa el carrito guardado en sesión
        ]);
    })->name('tramitar-pedido');

    Route::post('/api/tramitar-pedido', [PedidoController::class, 'store']);


    Route::get('/mis-pedidos', [PedidoController::class, 'index'])
        ->name('mis-pedidos');


    Route::get('/api/mis-pedidos', [PedidoController::class, 'getPedidos'])
        ->name('api.mis-pedidos');

    Route::get('/api/ver-pedido/{id}', [PedidoController::class, 'show'])->name('pedido.show');

    Route::post('/api/cancelar-pedido/{id}', [PedidoController::class, 'cancelar'])->name('pedido.cancelar');
});




















Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    // RUTAS DE ADMIN

    // Dashboard del administrador
    Route::get('/mi-gestion-admin', [AdminController::class, 'dashboard'])->name('mi-gestion-admin');



    Route::get('/opciones', function () {
        return Inertia::render('Admin/Opciones');
    })->name('opciones');

    // Ruta para la vista de citas del barbero en la interfaz de administración
    Route::get('/admin/citas', function () {
        return Inertia::render('Admin/CitasAdmin');
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
        return Inertia::render('Admin/MisDatosAdmin');
    })->name('mis-datos-admin');

    Route::get('/mis-datos-admin', [AdminController::class, 'misDatos'])->name('mis-datos-admin');

    // Ruta para actualizar datos del administrador
    Route::patch('/admin/actualizar-datos', [AdminController::class, 'actualizarDatos'])->name('admin.actualizar-datos');
    Route::post('/admin/actualizar-foto/{id}', [AdminController::class, 'actualizarFoto'])->name('admin.actualizar-foto');

    Route::get('/admin/asignar-servicios', function () {
        return Inertia::render('Admin/Asignar');
    })->name('admin.asignar.servicios');

    Route::post('/admin/asignar-servicios', [ServicioUsuarioController::class, 'asignarServicios'])
        ->name('admin.asignar-servicios');



    Route::get('/citas-barberia', function () {
        return Inertia::render('Admin/CitasBarberia');
    })->name('citas-barberia');

    Route::get('/admin/proveedores/nuevo', function () {
        return Inertia::render('Admin/NuevoProveedor');
    })->name('admin.proveedores.nuevo');

    Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');

    Route::delete('/trabajadores/{id}', [AdminController::class, 'destroy'])->name('trabajadores.destroy');

    Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->name('productos.destroy');

    Route::patch('/trabajadores/{id}/habilitar', [AdminController::class, 'habilitar'])->name('trabajadores.habilitar');
    Route::patch('/trabajadores/{id}/deshabilitar', [AdminController::class, 'deshabilitar'])->name('trabajadores.deshabilitar');

    Route::patch('/trabajadores/{id}/updateField', [AdminController::class, 'updateField'])->name('trabajadores.updateField');

    Route::post('/trabajadores/{id}/updatePhoto', [AdminController::class, 'updatePhoto'])->name('trabajadores.updatePhoto');

    Route::post('/productos/{id}/updatePhoto', [ProductoController::class, 'updatePhoto'])->name('productos.updatePhoto');

    Route::get('/candidatos/{oferta}', [CandidaturaController::class, 'verCandidatos'])->name('candidatos.index');
    Route::patch('/candidaturas/{id}/cambiar-estado', [CandidaturaController::class, 'cambiarEstado'])->name('candidaturas.cambiarEstado');

    Route::patch('/proveedores/{id}/updateField', [ProveedorController::class, 'updateField'])->name('proveedores.updateField');

    Route::patch('/productos/{id}/updateField', [ProductoController::class, 'updateField'])->name('productos.updateField');


    Route::get('/admin/productos/crear', function () {
        return Inertia::render('Admin/AltaProducto');
    })->name('admin.productos.crear');

    Route::post('/admin/productos', [ProductoController::class, 'store'])->name('productos.store');

    Route::get('/admin/productos/editar', function () {
        return Inertia::render('Admin/EditarProductos');
    })->name('admin.productos.editar');

    Route::post('/admin/desasignar-servicio', [ServicioUsuarioController::class, 'desasignarServicio']);
    Route::post('/admin/descansos', [DescansoController::class, 'store'])->name('admin.descansos.store');
    Route::post('/admin/dias-descanso', [AdminController::class, 'guardarDiasDescanso'])->name('admin.diasDescanso');

    Route::post('/admin/guardar-descanso-individual', [AdminController::class, 'guardarDescansoIndividual'])
        ->name('admin.guardar.descanso.individual');

    Route::get('/api/proveedores', [ProveedorController::class, 'obtenerProveedores']);

    Route::get('/api/admin/descansos', [DescansoController::class, 'getDescansos']);

    Route::get('/api/admin/descansos-individuales', [DescansoController::class, 'getDescansosIndividuales']);

    Route::get('/api/citas-barbero-actual', [CitaController::class, 'getCitasBarberoActual']);

    Route::get('/api/citas-todos', [CitaController::class, 'getCitasTodos']);



    Route::get('/api/asignar/barberos/{barbero}/servicios', [ServicioUsuarioController::class, 'getServiciosAsignados']);

    Route::get('/barbero/{id}/descansos-individuales', [CitaController::class, 'getDescansosIndividuales2']);


    Route::get('/clientes/{id}/ficha', [ClienteController::class, 'mostrarFicha'])->name('clientes.ficha');

    Route::get('/admin/pedidos', [PedidoController::class, 'pedidosAdminVista'])
        ->name('admin.pedidos');

    Route::get('/api/admin/pedidos', [PedidoController::class, 'getPedidosAdmin'])
        ->name('api.admin.pedidos');

    Route::get('/api/admin/ver-pedido/{id}', [PedidoController::class, 'show'])->name('pedido.admin.show');

    Route::patch('/api/admin/pedidos/{id}/estado', [PedidoController::class, 'actualizarEstado'])
    ->name('api.admin.pedidos.estado');

    Route::post('/api/admin/pedido/{id}/reembolso', [PedidoController::class, 'emitirReembolso'])
    ->name('admin.pedido.reembolso');

    Route::get('/admin/pedido/proveedor', function () {
        return Inertia::render('Admin/PedidosProveedores');
    })->name('admin.pedido.proveedor');


    Route::post('/api/admin/pedidos-proveedores', [PedidoController::class, 'realizarPedido']);

    Route::get('/admin/gestionpedido/proveedor', function () {
        return Inertia::render('Admin/PedidosInventario');
    })->name('admin.gestionpedido.proveedor');

    Route::get('/admin/pedidos-proveedores', [PedidoProveedorController::class, 'index'])
        ->name('admin.pedidos-proveedores');

    Route::patch('/admin/pedidos-proveedores/{codigo_pedido}/estado', [PedidoProveedorController::class, 'actualizarEstado'])
        ->name('admin.pedidos-proveedores.actualizarEstado');

    Route::post('/admin/pedidos-proveedores/{codigo_pedido}/añadir-stock', [PedidoProveedorController::class, 'añadirStock'])
    ->name('admin.pedidos-proveedores.añadirStock');

    Route::get('/caja', function () {
        return Inertia::render('Admin/ControlCaja');
    })->name('caja');

    Route::get('/api/caja', [CitaController::class, 'obtenerCaja']);

    Route::get('/api/beneficio-pedidos', [PedidoController::class, 'obtenerBeneficioPedidos']);

    Route::get('/api/gastos-proveedores', [PedidoController::class, 'obtenerGastosProveedores']);

    Route::get('/ganancias/personales', function () {
        return Inertia::render('Admin/GananciasPersonales');
    })->name('ganancias.personales');

    Route::get('/api/ganancias-barbero', [AdminController::class, 'obtenerGananciasBarbero']);

    Route::get('/api/productos-bajo-stock', [ProductoController::class, 'obtenerProductosBajoStock']);






});

















Route::middleware(['auth', 'verified', 'trabajador'])->group(function () {
    // RUTAS DE TRABAJADOR
    Route::get('/mi-cuenta-trabajador', [AdminController::class, 'dashboardTrabajador'])->name('mi-cuenta-trabajador');

    Route::get('/trabajador/citas/{fecha}', [AdminController::class, 'citasPorDia']);


    Route::get('/api/trabajador/descansos', [DescansoController::class, 'getDescansos']);

    Route::get('/api/trabajador/descansos-individuales', [DescansoController::class, 'getDescansosIndividuales']);

    Route::get('/trabajador/citas-barbero', [AdminController::class, 'citasBarberoTrabajador'])->name('trabajador.citas-barbero');

    Route::get('/trabajador/citas-barberia', [AdminController::class, 'citasBarberiaTrab'])->name('trabajador.citas-barberia');

    Route::patch('/trabajador/citas/{id}/cambiar-estado', [AdminController::class, 'cambiarEstado']);

    Route::patch('/trabajador/citas/{id}/cambiar-metodo-pago', [AdminController::class, 'cambiarMetodoPago']);

    Route::delete('/trabajador/citas/{id}/cancelar', [AdminController::class, 'cancelarCita']);

    Route::get('/opciones-trabajador', function () {
        return Inertia::render('Trabajador/OpcionesTrabajador');
    })->name('opciones-trabajador');

    Route::get('/trabajador/citas', function () {
        return Inertia::render('Trabajador/CitasAdminTrabajador');
    })->name('trabajador-citas');

    Route::get('/citas-barberia-trab', function () {
        return Inertia::render('Trabajador/CitasBarberiaTrabajador', [
            'auth' => ['user' => Auth::user()]
        ]);
    })->middleware(['auth'])->name('citas-barberia-trab');


    Route::get('/mis-datos-trabajador', function () {
        return Inertia::render('Trabajador/MisDatosAdminTrabajador', [
            'trabajador' => Auth::user(),
        ]);
    })->name('mis-datos-trabajador');


    // Foro de noticias del trabajador

    Route::get('/trabajador/foro', [NoticiaController::class, 'indexTrab'])->name('trabajador-foro'); //falta
    Route::post('/trabajador/foro', [NoticiaController::class, 'storeTrab'])->name('noticias2.store');
    Route::put('/trabajador/foro/{noticia}', [NoticiaController::class, 'updateTrab'])->name('noticias2.update');
    Route::delete('/trabajador/foro/{noticia}', [NoticiaController::class, 'destroyTrab'])->name('noticias2.destroy');

    Route::get('/trabajador/nuevos-servicios', [ServicioController::class, 'createTrab'])->name('trabajador.servicios.create');

    Route::post('/trabajador/servicios', [ServicioController::class, 'storeTrab'])->name('trabajador.servicios.store');


    Route::get('/trabajador/asignar-servicios', function () {
        return Inertia::render('Trabajador/AsignarTrab', [
            'auth' => ['user' => Auth::user()]
        ]);
    })->middleware(['auth'])->name('trabajador.asignar.servicios');

    Route::post('/trabajador/asignar-servicios', [ServicioUsuarioController::class, 'asignarServicios'])
        ->name('trabajador.asignar-servicios');

    Route::get('/api/trabajador/asignar/barberos/{barbero}/servicios', [ServicioUsuarioController::class, 'getServiciosAsignados']);

    Route::post('/trabajador/desasignar-servicio', [ServicioUsuarioController::class, 'desasignarServicio']);


    Route::patch('/trabajador/actualizar-datos', [AdminController::class, 'actualizarDatos'])->name('trabajador.actualizar-datos');
    Route::post('/trabajador/actualizar-foto/{id}', [AdminController::class, 'actualizarFoto'])->name('trabajador.actualizar-foto');

    Route::get('/trabajador/pedidos', [PedidoController::class, 'pedidosTrabVista'])
        ->name('trabajador.pedidos');

    Route::get('/api/trabajador/pedidos', [PedidoController::class, 'getPedidosAdmin'])
        ->name('api.trabajador.pedidos');

    Route::patch('/api/trabajador/pedidos/{id}/estado', [PedidoController::class, 'actualizarEstado'])
        ->name('api.trabajador.pedidos.estado');

    Route::post('/api/trabajador/pedido/{id}/reembolso', [PedidoController::class, 'emitirReembolso'])
        ->name('trabajador.pedido.reembolso');

    Route::get('/api/trabajador/ver-pedido/{id}', [PedidoController::class, 'show'])->name('pedido.trabajador.show');

    Route::get('/ganancias/trabajador/personales', function () {
        return Inertia::render('Trabajador/GananciasPersonalesTrabajador');
    })->name('ganancias.trabajador.personales');

    Route::get('/api/ganancias-trabajador', [AdminController::class, 'obtenerGananciasBarbero']);

    Route::get('/api/barbero-logueado', [AdminController::class, 'obtenerBarberoLogueado']);

    Route::get('/inventario/trabajador', function () {
        return Inertia::render('Trabajador/PedidosInventarioTrabajador');
    })->name('inventario.trabajador');



    Route::get('/trabajador/pedidos-proveedores', [PedidoProveedorController::class, 'index'])
        ->name('trabajador.pedidos-proveedores');

    Route::patch('/trabajador/pedidos-proveedores/{codigo_pedido}/estado', [PedidoProveedorController::class, 'actualizarEstado'])
        ->name('trabajador.pedidos-proveedores.actualizarEstado');

    Route::post('/trabajador/pedidos-proveedores/{codigo_pedido}/añadir-stock', [PedidoProveedorController::class, 'añadirStock'])
    ->name('trabajador.pedidos-proveedores.añadirStock');


    Route::get('/api/trab/productos-bajo-stock', [ProductoController::class, 'obtenerProductosBajoStock']);




});

















// RUTAS DE ACCESO A DATOS
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard del cliente
    Route::get('/mi-cuenta', function () {
        if (auth()->user()->rol === 'admin') {
            return redirect()->route('mi-gestion-admin');
        } elseif (auth()->user()->rol === 'trabajador') {
            return redirect()->route('mi-cuenta-trabajador');
        }
        return Inertia::render('Cliente/Dashboard', ['user' => auth()->user()]);
    })->name('mi-cuenta');


    Route::get('/api/servicios', [ServicioController::class, 'index'])->name('servicios.index');


    Route::get('/api/barberos', [AdminController::class, 'obtenerBarberos']);

    Route::get('/api/citas-usuario', [CitaController::class, 'obtenerCitasUsuario']);   //para pintar dias



    Route::get('/api/barberos/{barberoId}/servicios', [AdminController::class, 'getServiciosPorBarbero'])
        ->name('barberos.servicios');


    Route::get('/api/productos', [ProductoController::class, 'obtenerProductos'])->name('productos.obtener');
});





















// RUTAS PÚBLICAS

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

Route::get('/api/public/citas/disponibilidad', [RecompensaController::class, 'disponibilidad'])->name('public.citas.disponibilidad');
Route::get('/api/public/descansos', [DescansoController::class, 'getDescansos'])->name('descansos.public.dispo');
Route::get('/api/public/descansos/{barberoId}', [DescansoController::class, 'obtenerDiasDescanso']);

Route::get('/api/public/barberos', [AdminController::class, 'obtenerBarberos']);
Route::get('/api/public/servicios', [ServicioController::class, 'index'])->name('servicios.public.index');
Route::get('/api/public/barberos/{barberoId}/servicios', [RecompensaController::class, 'getServiciosPorBarbero'])
    ->name('public.barberos.servicios');


Route::post('/guardar-ruta-sesion', function (Illuminate\Http\Request $request) {
    session(['ruta_despues_login' => $request->ruta]);
    return response()->json(['success' => true]);       // Variable de sesión trabajar
});


Route::get('/barbershopinvitado', function () {
    return Inertia::render('ShopInvitado');
})->name('barbershopinvitado');


Route::get('/api/public/productos', [ProductoController::class, 'obtenerProductos'])->name('public.productos.obtener');

Route::get('/trabajadores-fotos', [AdminController::class, 'getTrabajadores']);
