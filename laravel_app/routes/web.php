<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CelulasController;
use App\Http\Controllers\EncuentrosController;
use App\Http\Controllers\EventosController;
use App\Http\Controllers\LideresController;
use App\Http\Controllers\MinisteriosController;
use App\Http\Controllers\PersonasController;
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
Route::delete('/personas/{persona}', [PersonasController::class, 'destroy'])->name('personas.destroy');

Route::get('/lideres', [LideresController::class, 'index'])->name('lideres.index');
Route::get('/lideres/create', [LideresController::class, 'create'])->name('lideres.create');
Route::post('/lideres', [LideresController::class, 'store'])->name('lideres.store');
Route::get('/lideres/{lider}', [LideresController::class, 'show'])->name('lideres.show');
Route::get('/lideres/{lider}/edit', [LideresController::class, 'edit'])->name('lideres.edit');
Route::put('/lideres/{lider}', [LideresController::class, 'update'])->name('lideres.update');
Route::delete('/lideres/{lider}', [LideresController::class, 'destroy'])->name('lideres.destroy');

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
