<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CelulasController;
use App\Http\Controllers\EncuentrosController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\LideresController;
use App\Http\Controllers\MinisteriosController;
use App\Http\Controllers\PersonasController;
use App\Http\Controllers\RedesController;
use App\Http\Controllers\SedeActivaController;
use App\Http\Controllers\SedesController;
use App\Http\Controllers\SinLiderController;
use App\Http\Controllers\TrasladosController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

Route::get('/users', [UsersController::class, 'index'])->name('users.index');
Route::get('/users/create', [UsersController::class, 'create'])->name('users.create');
Route::post('/users', [UsersController::class, 'store'])->name('users.store');
Route::get('/users/{user}/edit', [UsersController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UsersController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UsersController::class, 'destroy'])->name('users.destroy');

Route::get('/personas', [PersonasController::class, 'index'])->name('personas.index');
Route::get('/personas/create', [PersonasController::class, 'create'])->name('personas.create');
Route::post('/personas', [PersonasController::class, 'store'])->name('personas.store');
Route::get('/personas/{persona}', [PersonasController::class, 'show'])->name('personas.show');
Route::get('/personas/{persona}/edit', [PersonasController::class, 'edit'])->name('personas.edit');
Route::put('/personas/{persona}', [PersonasController::class, 'update'])->name('personas.update');
Route::get('/personas/{persona}/trasladar', [TrasladosController::class, 'create'])->name('traslados.create');
Route::post('/personas/{persona}/trasladar', [TrasladosController::class, 'store'])->name('traslados.store');
Route::post('/personas/{persona}/inactivar', [PersonasController::class, 'inactivar'])->name('personas.inactivar');
Route::post('/personas/{persona}/reactivar', [PersonasController::class, 'reactivar'])->name('personas.reactivar');
Route::delete('/personas/{persona}', [PersonasController::class, 'destroy'])->name('personas.destroy');

Route::get('/lideres', [LideresController::class, 'index'])->name('lideres.index');
Route::get('/lideres/designar', [LideresController::class, 'designar'])->name('lideres.designar');
Route::post('/lideres/designar', [LideresController::class, 'storeDesignar'])->name('lideres.designar.store');
Route::get('/lideres/{lider}', [LideresController::class, 'show'])->name('lideres.show');
Route::get('/lideres/{lider}/quitar', [LideresController::class, 'quitar'])->name('lideres.quitar');
Route::delete('/lideres/{lider}', [LideresController::class, 'destroy'])->name('lideres.destroy');

Route::post('/sede-activa', [SedeActivaController::class, 'update'])->name('sede-activa.update');

Route::get('/sedes', [SedesController::class, 'index'])->name('sedes.index');
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

Route::get('/sin-lider', [SinLiderController::class, 'index'])->name('sin-lider.index');
Route::post('/sin-lider/{persona}/asignar', [SinLiderController::class, 'asignar'])->name('sin-lider.asignar');

Route::get('/redes', [RedesController::class, 'index'])->name('redes.index');
Route::post('/redes', [RedesController::class, 'store'])->name('redes.store');
Route::put('/redes/{red}', [RedesController::class, 'update'])->name('redes.update');
Route::post('/redes/{red}/fusionar', [RedesController::class, 'fusionar'])->name('redes.fusionar');
Route::delete('/redes/{red}', [RedesController::class, 'destroy'])->name('redes.destroy');


Route::get('/encuentros', [EncuentrosController::class, 'index'])->name('encuentros.index');
Route::get('/encuentros/create', [EncuentrosController::class, 'create'])->name('encuentros.create');
Route::post('/encuentros', [EncuentrosController::class, 'store'])->name('encuentros.store');
Route::get('/encuentros/{encuentro}', [EncuentrosController::class, 'show'])->name('encuentros.show');
Route::get('/encuentros/{encuentro}/edit', [EncuentrosController::class, 'edit'])->name('encuentros.edit');
Route::put('/encuentros/{encuentro}', [EncuentrosController::class, 'update'])->name('encuentros.update');
Route::put('/encuentros/{encuentro}/asistencia', [EncuentrosController::class, 'actualizarAsistencia'])->name('encuentros.asistencia');
Route::delete('/encuentros/{encuentro}', [EncuentrosController::class, 'destroy'])->name('encuentros.destroy');

Route::get('/eventos', [EventosController::class, 'index'])->name('eventos.index');
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

Route::get('/ministerios', [MinisteriosController::class, 'index'])->name('ministerios.index');
Route::get('/ministerios/create', [MinisteriosController::class, 'create'])->name('ministerios.create');
Route::post('/ministerios', [MinisteriosController::class, 'store'])->name('ministerios.store');
Route::get('/ministerios/{ministerio}', [MinisteriosController::class, 'show'])->name('ministerios.show');
Route::get('/ministerios/{ministerio}/edit', [MinisteriosController::class, 'edit'])->name('ministerios.edit');
Route::put('/ministerios/{ministerio}', [MinisteriosController::class, 'update'])->name('ministerios.update');
Route::put('/ministerios/{ministerio}/integrantes', [MinisteriosController::class, 'actualizarIntegrantes'])->name('ministerios.integrantes');
Route::delete('/ministerios/{ministerio}', [MinisteriosController::class, 'destroy'])->name('ministerios.destroy');

Route::get('/celulas', [CelulasController::class, 'index'])->name('celulas.index');
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
