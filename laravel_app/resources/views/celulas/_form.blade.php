@php
    $celula = $celula ?? null;
@endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Nombre del grupo</label>
            <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $celula?->nombre) }}" placeholder="Ej. Célula Los Olivos" required>
            @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Sede</label>
            <input type="text" name="sede" class="form-control @error('sede') is-invalid @enderror" value="{{ old('sede', $celula?->sede) }}">
            @error('sede')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Estado</label>
            <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
                @foreach(\App\Models\Celula::ESTADOS as $estado)
                    <option value="{{ $estado }}" {{ old('estado', $celula?->estado ?? 'activa') == $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                @endforeach
            </select>
            @error('estado')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Líder</label>
            <select name="lider_id" class="form-control @error('lider_id') is-invalid @enderror" required>
                <option value="">Seleccione...</option>
                @foreach($personas as $persona)
                    <option value="{{ $persona->id }}" {{ (string) old('lider_id', $celula?->lider_id) === (string) $persona->id ? 'selected' : '' }}>{{ $persona->nombre_completo }}</option>
                @endforeach
            </select>
            @error('lider_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Co-líder</label>
            <select name="co_lider_id" class="form-control @error('co_lider_id') is-invalid @enderror">
                <option value="">Sin co-líder</option>
                @foreach($personas as $persona)
                    <option value="{{ $persona->id }}" {{ (string) old('co_lider_id', $celula?->co_lider_id) === (string) $persona->id ? 'selected' : '' }}>{{ $persona->nombre_completo }}</option>
                @endforeach
            </select>
            @error('co_lider_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Día de reunión</label>
            <select name="dia_reunion" class="form-control @error('dia_reunion') is-invalid @enderror">
                <option value="">Sin definir</option>
                @foreach(\App\Models\Celula::DIAS as $dia)
                    <option value="{{ $dia }}" {{ old('dia_reunion', $celula?->dia_reunion) == $dia ? 'selected' : '' }}>{{ $dia }}</option>
                @endforeach
            </select>
            @error('dia_reunion')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Hora de reunión</label>
            <input type="time" name="hora_reunion" class="form-control @error('hora_reunion') is-invalid @enderror" value="{{ old('hora_reunion', $celula?->hora_reunion ? substr($celula->hora_reunion, 0, 5) : '') }}">
            @error('hora_reunion')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Lugar de reunión</label>
            <input type="text" name="lugar_reunion" class="form-control @error('lugar_reunion') is-invalid @enderror" value="{{ old('lugar_reunion', $celula?->lugar_reunion) }}">
            @error('lugar_reunion')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
