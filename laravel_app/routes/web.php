<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvancesNivelController;
use App\Http\Controllers\CelulasController;
use App\Http\Controllers\EncuentrosController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\LideresController;
use App\Http\Controllers\MinisteriosController;
use App\Http\Controllers\NivelesController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\RegistroMiembrosPublicoController;
use App\Http\Controllers\RegistroMiembrosQrController;
use App\Http\Controllers\RegistroPublicoController;
use App\Http\Controllers\RegistroQrController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SedeActivaController;
use App\Http\Controllers\SedesController;
use App\Http\Controllers\SinLiderController;
use App\Http\Controllers\TrasladosController;
use App\Http\Controllers\ClavesUsuarioController;
use App\Http\Controllers\CuentaController;
use App\Http\Controllers\ControlCambiosController;
use App\Http\Controllers\RecuperacionClaveController;
use App\Http\Controllers\SolicitudesClaveController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
 * Control de acceso (spec 013): cada grupo declara su módulo con `modulo:{clave}` (GET exige lectura; los demás
 * métodos y las pantallas create/edit exigen escritura) y lo reservado al administrador usa `administrador`.
 * Las rutas públicas y las de la cuenta propia no llevan módulo. Una prueba falla si una ruta con sesión
 * no pertenece a ninguna de estas categorías.
 */

// Públicas y de la cuenta propia.
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/olvide-clave', [RecuperacionClaveController::class, 'create'])->name('recuperacion.create');
Route::post('/olvide-clave', [RecuperacionClaveController::class, 'store'])->name('recuperacion.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/cambiar-clave', [CuentaController::class, 'editarClave'])->name('cambiar-clave.edit');
Route::put('/cambiar-clave', [CuentaController::class, 'actualizarClave'])->name('cambiar-clave.update');
Route::get('/mi-cuenta', [CuentaController::class, 'mostrar'])->name('cuenta.edit');
Route::put('/mi-cuenta/clave', [CuentaController::class, 'cambiarClave'])->name('cuenta.clave.update');

Route::middleware('modulo:tablero')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
});

Route::middleware('modulo:usuarios')->group(function () {
    Route::get('/users', [UsersController::class, 'index'])->name('users.index');
    Route::get('/users/datos', [UsersController::class, 'datos'])->name('users.datos');
    Route::get('/users/exportar', [UsersController::class, 'exportar'])->name('users.exportar');
    Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{user}/clave', [ClavesUsuarioController::class, 'store'])->name('users.clave.store');
    Route::post('/claves/{registro}/enviada', [ClavesUsuarioController::class, 'enviada'])->name('claves.enviada');
});

Route::middleware('administrador')->group(function () {
    Route::get('/roles', [RolesController::class, 'index'])->name('roles.index');
    Route::get('/roles/datos', [RolesController::class, 'datos'])->name('roles.datos');
    Route::get('/roles/exportar', [RolesController::class, 'exportar'])->name('roles.exportar');
    Route::get('/roles/create', [RolesController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RolesController::class, 'store'])->name('roles.store');
    Route::get('/roles/{rol}', [RolesController::class, 'show'])->name('roles.show');
    Route::get('/roles/{rol}/edit', [RolesController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{rol}', [RolesController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{rol}', [RolesController::class, 'destroy'])->name('roles.destroy');

    Route::post('/sede-activa', [SedeActivaController::class, 'update'])->name('sede-activa.update');

    Route::get('/sedes', [SedesController::class, 'index'])->name('sedes.index');
    Route::get('/sedes/datos', [SedesController::class, 'datos'])->name('sedes.datos');
    Route::get('/sedes/exportar', [SedesController::class, 'exportar'])->name('sedes.exportar');
    Route::get('/sedes/create', [SedesController::class, 'create'])->name('sedes.create');
    Route::post('/sedes', [SedesController::class, 'store'])->name('sedes.store');
    Route::get('/sedes/{sede}', [SedesController::class, 'show'])->name('sedes.show');
    Route::get('/sedes/{sede}/edit', [SedesController::class, 'edit'])->name('sedes.edit');
    Route::put('/sedes/{sede}', [SedesController::class, 'update'])->name('sedes.update');
    Route::get('/sedes/{sede}/pastores', [SedesController::class, 'pastores'])->name('sedes.pastores');
    Route::put('/sedes/{sede}/pastores', [SedesController::class, 'updatePastores'])->name('sedes.pastores.update');
    Route::post('/sedes/{sede}/desactivar', [SedesController::class, 'desactivar'])->name('sedes.desactivar');
    Route::post('/sedes/{sede}/reactivar', [SedesController::class, 'reactivar'])->name('sedes.reactivar');
    Route::delete('/sedes/{sede}', [SedesController::class, 'destroy'])->name('sedes.destroy');

    Route::get('/personas/{persona}/trasladar', [TrasladosController::class, 'create'])->name('traslados.create');
    Route::post('/personas/{persona}/trasladar', [TrasladosController::class, 'store'])->name('traslados.store');
});

Route::middleware('modulo:solicitudes_clave')->group(function () {
    Route::get('/solicitudes-clave', [SolicitudesClaveController::class, 'index'])->name('solicitudes-clave.index');
    Route::get('/solicitudes-clave/datos', [SolicitudesClaveController::class, 'datos'])->name('solicitudes-clave.datos');
    Route::get('/solicitudes-clave/exportar', [SolicitudesClaveController::class, 'exportar'])->name('solicitudes-clave.exportar');
    Route::post('/solicitudes-clave/{solicitud}/atender', [SolicitudesClaveController::class, 'atender'])->name('solicitudes-clave.atender');
    Route::post('/solicitudes-clave/{solicitud}/descartar', [SolicitudesClaveController::class, 'descartar'])->name('solicitudes-clave.descartar');
});

// Control de cambios (spec 015): solo lectura y sin exportación; el historial no se modifica por ninguna ruta.
Route::middleware('modulo:control_cambios')->group(function () {
    Route::get('/control-cambios', [ControlCambiosController::class, 'index'])->name('control-cambios.index');
    Route::get('/control-cambios/datos', [ControlCambiosController::class, 'datos'])->name('control-cambios.datos');
});

Route::middleware('modulo:miembros')->group(function () {
    Route::get('/personas', [PersonasController::class, 'index'])->name('personas.index');
    Route::get('/personas/create', [PersonasController::class, 'create'])->name('personas.create');
    Route::get('/personas/buscar', [PersonasController::class, 'buscar'])->name('personas.buscar');
    Route::get('/personas/datos', [PersonasController::class, 'datos'])->name('personas.datos');
    Route::get('/personas/exportar', [PersonasController::class, 'exportar'])->name('personas.exportar');
    Route::post('/personas/rapida', [PersonasController::class, 'rapida'])->name('personas.rapida');
    Route::post('/personas', [PersonasController::class, 'store'])->name('personas.store');
    Route::get('/personas/{persona}', [PersonasController::class, 'show'])->name('personas.show');
    Route::get('/personas/{persona}/edit', [PersonasController::class, 'edit'])->name('personas.edit');
    Route::put('/personas/{persona}', [PersonasController::class, 'update'])->name('personas.update');
    Route::post('/personas/{persona}/inactivar', [PersonasController::class, 'inactivar'])->name('personas.inactivar');
    Route::post('/personas/{persona}/reactivar', [PersonasController::class, 'reactivar'])->name('personas.reactivar');
    Route::delete('/personas/{persona}', [PersonasController::class, 'destroy'])->name('personas.destroy');
});

Route::middleware('modulo:lideres')->group(function () {
    Route::get('/lideres', [LideresController::class, 'index'])->name('lideres.index');
    Route::get('/lideres/datos', [LideresController::class, 'datos'])->name('lideres.datos');
    Route::get('/lideres/exportar', [LideresController::class, 'exportar'])->name('lideres.exportar');
    Route::get('/lideres/designar', [LideresController::class, 'designar'])->name('lideres.designar');
    Route::post('/lideres/designar', [LideresController::class, 'storeDesignar'])->name('lideres.designar.store');
    Route::get('/lideres/{lider}', [LideresController::class, 'show'])->name('lideres.show');
    Route::get('/lideres/{lider}/quitar', [LideresController::class, 'quitar'])->name('lideres.quitar');
    Route::delete('/lideres/{lider}', [LideresController::class, 'destroy'])->name('lideres.destroy');
});

Route::middleware('modulo:sin_lider')->group(function () {
    Route::get('/sin-lider', [SinLiderController::class, 'index'])->name('sin-lider.index');
    Route::get('/sin-lider/datos', [SinLiderController::class, 'datos'])->name('sin-lider.datos');
    Route::get('/sin-lider/exportar', [SinLiderController::class, 'exportar'])->name('sin-lider.exportar');
    Route::post('/sin-lider/{persona}/asignar', [SinLiderController::class, 'asignar'])->name('sin-lider.asignar');
});

Route::middleware('modulo:encuentros')->group(function () {
    Route::get('/encuentros', [EncuentrosController::class, 'index'])->name('encuentros.index');
    Route::get('/encuentros/datos', [EncuentrosController::class, 'datos'])->name('encuentros.datos');
    Route::get('/encuentros/exportar', [EncuentrosController::class, 'exportar'])->name('encuentros.exportar');
    Route::get('/encuentros/create', [EncuentrosController::class, 'create'])->name('encuentros.create');
    Route::post('/encuentros', [EncuentrosController::class, 'store'])->name('encuentros.store');
    Route::get('/encuentros/{encuentro}', [EncuentrosController::class, 'show'])->name('encuentros.show');
    Route::get('/encuentros/{encuentro}/edit', [EncuentrosController::class, 'edit'])->name('encuentros.edit');
    Route::put('/encuentros/{encuentro}', [EncuentrosController::class, 'update'])->name('encuentros.update');
    Route::put('/encuentros/{encuentro}/asistencia', [EncuentrosController::class, 'actualizarAsistencia'])->name('encuentros.asistencia');
    Route::delete('/encuentros/{encuentro}', [EncuentrosController::class, 'destroy'])->name('encuentros.destroy');
});

Route::middleware('modulo:niveles')->group(function () {
    Route::get('/niveles', [NivelesController::class, 'index'])->name('niveles.index');
    Route::get('/niveles/create', [NivelesController::class, 'create'])->name('niveles.create');
    Route::post('/niveles', [NivelesController::class, 'store'])->name('niveles.store');
    Route::get('/niveles/miembros', [NivelesController::class, 'miembros'])->name('niveles.miembros.index');
    Route::get('/niveles/miembros/datos', [NivelesController::class, 'datos'])->name('niveles.miembros.datos');
    Route::get('/niveles/miembros/exportar', [NivelesController::class, 'exportar'])->name('niveles.miembros.exportar');
    Route::post('/niveles/avances', [AvancesNivelController::class, 'store'])->name('niveles.avances.store');
    Route::delete('/niveles/avances/{avance}', [AvancesNivelController::class, 'destroy'])->name('niveles.avances.destroy');
    Route::get('/niveles/{nivel}/edit', [NivelesController::class, 'edit'])->name('niveles.edit');
    Route::put('/niveles/{nivel}', [NivelesController::class, 'update'])->name('niveles.update');
    Route::post('/niveles/{nivel}/subir', [NivelesController::class, 'subir'])->name('niveles.subir');
    Route::post('/niveles/{nivel}/bajar', [NivelesController::class, 'bajar'])->name('niveles.bajar');
    Route::post('/niveles/{nivel}/desactivar', [NivelesController::class, 'desactivar'])->name('niveles.desactivar');
    Route::post('/niveles/{nivel}/reactivar', [NivelesController::class, 'reactivar'])->name('niveles.reactivar');
    Route::delete('/niveles/{nivel}', [NivelesController::class, 'destroy'])->name('niveles.destroy');
});

Route::middleware('modulo:eventos')->group(function () {
    Route::get('/eventos', [EventosController::class, 'index'])->name('eventos.index');
    Route::get('/eventos/datos', [EventosController::class, 'datos'])->name('eventos.datos');
    Route::get('/eventos/exportar', [EventosController::class, 'exportar'])->name('eventos.exportar');
    Route::get('/eventos/create', [EventosController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventosController::class, 'store'])->name('eventos.store');
    Route::get('/eventos/{evento}', [EventosController::class, 'show'])->name('eventos.show');
    Route::get('/eventos/{evento}/edit', [EventosController::class, 'edit'])->name('eventos.edit');
    Route::put('/eventos/{evento}', [EventosController::class, 'update'])->name('eventos.update');
    Route::delete('/eventos/{evento}', [EventosController::class, 'destroy'])->name('eventos.destroy');

    Route::post('/eventos/{evento}/personas', [EventosController::class, 'agregarPersona'])->name('eventos.personas.store');
    Route::delete('/eventos/{evento}/personas/{inscripcion}', [EventosController::class, 'quitarPersona'])->name('eventos.personas.destroy');

    Route::post('/eventos/{evento}/personas/{inscripcion}/pagos', [EventosController::class, 'registrarPago'])->name('eventos.pagos.store');
    Route::delete('/eventos/{evento}/personas/{inscripcion}/pagos/{pago}', [EventosController::class, 'eliminarPago'])->name('eventos.pagos.destroy');
});

Route::middleware('modulo:ministerios')->group(function () {
    Route::get('/ministerios', [MinisteriosController::class, 'index'])->name('ministerios.index');
    Route::get('/ministerios/datos', [MinisteriosController::class, 'datos'])->name('ministerios.datos');
    Route::get('/ministerios/exportar', [MinisteriosController::class, 'exportar'])->name('ministerios.exportar');
    Route::get('/ministerios/create', [MinisteriosController::class, 'create'])->name('ministerios.create');
    Route::post('/ministerios', [MinisteriosController::class, 'store'])->name('ministerios.store');
    Route::get('/ministerios/{ministerio}', [MinisteriosController::class, 'show'])->name('ministerios.show');
    Route::get('/ministerios/{ministerio}/edit', [MinisteriosController::class, 'edit'])->name('ministerios.edit');
    Route::put('/ministerios/{ministerio}', [MinisteriosController::class, 'update'])->name('ministerios.update');
    Route::put('/ministerios/{ministerio}/integrantes', [MinisteriosController::class, 'actualizarIntegrantes'])->name('ministerios.integrantes');
    Route::delete('/ministerios/{ministerio}', [MinisteriosController::class, 'destroy'])->name('ministerios.destroy');
});

Route::middleware('modulo:celulas')->group(function () {
    Route::get('/celulas', [CelulasController::class, 'index'])->name('celulas.index');
    Route::get('/celulas/datos', [CelulasController::class, 'datos'])->name('celulas.datos');
    Route::get('/celulas/exportar', [CelulasController::class, 'exportar'])->name('celulas.exportar');
    Route::get('/celulas/create', [CelulasController::class, 'create'])->name('celulas.create');
    Route::post('/celulas', [CelulasController::class, 'store'])->name('celulas.store');
    Route::get('/celulas/{celula}', [CelulasController::class, 'show'])->name('celulas.show');
    Route::get('/celulas/{celula}/edit', [CelulasController::class, 'edit'])->name('celulas.edit');
    Route::put('/celulas/{celula}', [CelulasController::class, 'update'])->name('celulas.update');
    Route::delete('/celulas/{celula}', [CelulasController::class, 'destroy'])->name('celulas.destroy');

    Route::post('/celulas/{celula}/miembros', [CelulasController::class, 'agregarMiembro'])->name('celulas.miembros.store');
    Route::delete('/celulas/{celula}/miembros/{persona}', [CelulasController::class, 'quitarMiembro'])->name('celulas.miembros.destroy');

    Route::post('/celulas/{celula}/reuniones', [CelulasController::class, 'registrarReunion'])->name('celulas.reuniones.store');
    Route::delete('/celulas/{celula}/reuniones/{reunion}', [CelulasController::class, 'eliminarReunion'])->name('celulas.reuniones.destroy');
});

// Registro por QR: formulario público (sin sesión) y gestión del QR.
Route::get('/registro/{token}', [RegistroPublicoController::class, 'show'])->name('registro.show');
Route::post('/registro/{token}/buscar', [RegistroPublicoController::class, 'buscar'])->name('registro.buscar');
Route::post('/registro/{token}', [RegistroPublicoController::class, 'store'])->name('registro.store');
Route::get('/registro/{token}/listo', [RegistroPublicoController::class, 'listo'])->name('registro.listo');

Route::get('/registro-sede/{token}', [RegistroMiembrosPublicoController::class, 'show'])->name('registro-sede.show');
Route::post('/registro-sede/{token}/buscar', [RegistroMiembrosPublicoController::class, 'buscar'])->name('registro-sede.buscar');
Route::post('/registro-sede/{token}', [RegistroMiembrosPublicoController::class, 'store'])->name('registro-sede.store');
Route::get('/registro-sede/{token}/listo', [RegistroMiembrosPublicoController::class, 'listo'])->name('registro-sede.listo');

Route::middleware('modulo:registro_miembros')->group(function () {
    Route::get('/registro-miembros', [RegistroMiembrosQrController::class, 'index'])->name('registro-miembros.index');
    Route::get('/sedes/{sede}/registro-miembros/qr.svg', [RegistroMiembrosQrController::class, 'svg'])->name('registro-miembros.svg');
    Route::post('/sedes/{sede}/registro-miembros/abrir', [RegistroMiembrosQrController::class, 'abrir'])->name('registro-miembros.abrir');
    Route::post('/sedes/{sede}/registro-miembros/regenerar', [RegistroMiembrosQrController::class, 'regenerar'])->name('registro-miembros.regenerar');
});

// QR de eventos, encuentros y células: el módulo depende del destino y lo decide el gate `gestionarRegistroQr`.
Route::get('/registro-qr/{tipo}/{id}/qr.svg', [RegistroQrController::class, 'svg'])->name('registro-qr.svg');
Route::post('/registro-qr/{tipo}/{id}/abrir', [RegistroQrController::class, 'abrir'])->name('registro-qr.abrir');
Route::post('/registro-qr/{tipo}/{id}/regenerar', [RegistroQrController::class, 'regenerar'])->name('registro-qr.regenerar');
