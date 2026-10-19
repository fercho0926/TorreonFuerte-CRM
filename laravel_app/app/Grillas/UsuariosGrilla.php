<?php

namespace App\Grillas;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

/** Listado de usuarios. Contiene datos de acceso: solo un administrador puede exportarlo. */
class UsuariosGrilla extends Grilla
{
    public function id(): string
    {
        return 'users';
    }

    public function extras(): array
    {
        return ['sede_id'];
    }

    public function conBusqueda(): bool
    {
        return true;
    }

    public function vacio(): array
    {
        return ['mensaje' => 'No hay usuarios registrados.', 'icono' => 'fa-users'];
    }

    public function puedeVer(?User $usuario): bool
    {
        return $usuario && Gate::forUser($usuario)->allows('verUsuarios', User::class);
    }

    public function puedeExportar(?User $usuario): bool
    {
        return $this->puedeVer($usuario) && $usuario->esAdmin();
    }

    protected function definirColumnas(): array
    {
        return [
            Columna::make('nombre', 'Nombre')->ordena('users.name')->filtraTexto('users.name')->valor(fn (User $u) => $u->name)->prioridad(1),
            Columna::make('email', 'Email')->ordena('users.email')->filtraTexto('users.email')->valor(fn (User $u) => $u->email)->prioridad(3),
            Columna::make('rol', 'Rol')
                ->ordena('users.role')
                ->filtraLista(User::ROLE_LABELS, 'users.role')
                ->valor(fn (User $u) => User::ROLE_LABELS[$u->role] ?? $u->role)
                ->prioridad(2),
            Columna::sede('users')
                ->valor(fn (User $u) => $u->getRelationValue('sede')?->nombre ?? 'Todas (administrador)')
                ->visibleSi(fn (?User $a) => $a && $a->esAdmin()),
            Columna::make('persona', 'Persona vinculada')
                ->ordenaPersona('users', 'persona_id')
                ->filtraPersona('persona')
                ->valor(fn (User $u) => $u->persona?->nombre_completo)
                ->prioridad(5),
            Columna::make('clave', 'Clave')->valor(fn (User $u) => '')->sinExportar()->prioridad(6),
            Columna::make('registrado', 'Registrado')->ordena('users.created_at')->valor(fn (User $u) => $u->created_at?->format('Y-m-d H:i'))->prioridad(7),
            Columna::acciones(),
        ];
    }

    public function ordenInicial(): array
    {
        return ['registrado', 'desc'];
    }

    protected function base(?User $usuario, array $estado): Builder|Collection
    {
        return User::query()
            ->with(['persona', 'sede', 'ultimoRegistroClave'])
            ->when($usuario?->esAdmin() && filled($estado['sede_id'] ?? null), fn ($q) => $q->where('users.sede_id', $estado['sede_id']));
    }

    protected function buscar(Builder|Collection $consulta, string $q): Builder|Collection
    {
        $like = '%'.str_replace(['%', '_'], '', $q).'%';

        return $consulta->where(fn ($w) => $w->where('users.name', 'like', $like)->orWhere('users.email', 'like', $like));
    }
}
