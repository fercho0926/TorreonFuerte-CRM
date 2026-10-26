{{-- Pestañas del módulo Usuarios. La pestaña «Roles» solo la ve el administrador. Variable: $activa ('usuarios' o 'roles'). --}}
@puede('administrador')
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item"><a class="nav-link @if($activa === 'usuarios') active @endif" @if($activa === 'usuarios') aria-current="page" @endif href="{{ route('users.index') }}"><i class="fas fa-users" aria-hidden="true"></i> Usuarios</a></li>
        <li class="nav-item"><a class="nav-link @if($activa === 'roles') active @endif" @if($activa === 'roles') aria-current="page" @endif href="{{ route('roles.index') }}"><i class="fas fa-user-shield" aria-hidden="true"></i> Roles</a></li>
    </ul>
@endpuede
