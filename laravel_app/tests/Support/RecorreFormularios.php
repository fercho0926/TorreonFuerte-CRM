<?php

namespace Tests\Support;

/**
 * Inventario de pantallas con formularios para las pruebas de la renovación de formularios.
 * Requiere los traits IniciaSesion, ConSedes y EscenarioDosSedes en la clase de prueba.
 */
trait RecorreFormularios
{
    /** Arma dos sedes con datos y entra como administrador trabajando en la sede A. */
    protected function prepararFormularios(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    /**
     * Pantallas de formulario completas (GET) del inventario de data-model.md.
     *
     * @return array<string, string> etiqueta => URL
     */
    protected function pantallasDeFormulario(): array
    {
        return [
            'persona (crear)' => route('personas.create'),
            'persona (editar)' => route('personas.edit', $this->A['persona']),
            'líder (designar)' => route('lideres.designar', ['q' => 'Persona']),
            'líder (quitar rol)' => route('lideres.quitar', $this->A['lider']),
            'célula (crear)' => route('celulas.create'),
            'célula (editar)' => route('celulas.edit', $this->A['celula']),
            'encuentro (crear)' => route('encuentros.create'),
            'encuentro (editar)' => route('encuentros.edit', $this->A['encuentro']),
            'evento (crear)' => route('eventos.create'),
            'evento (editar)' => route('eventos.edit', $this->A['evento']),
            'ministerio (crear)' => route('ministerios.create'),
            'ministerio (editar)' => route('ministerios.edit', $this->A['ministerio']),
            'usuario (crear)' => route('users.create'),
            'usuario (editar)' => route('users.edit', $this->A['liderUser']),
            'sede (crear)' => route('sedes.create'),
            'sede (editar)' => route('sedes.edit', $this->A['sede']),
            'sede (pastores)' => route('sedes.pastores', $this->A['sede']),
            'traslado' => route('traslados.create', $this->A['persona']),
        ];
    }

    /**
     * Pantallas con formularios incrustados o filtros (GET).
     *
     * @return array<string, string>
     */
    protected function pantallasConFormulariosIncrustados(): array
    {
        return [
            'célula (ficha)' => route('celulas.show', $this->A['celula']),
            'encuentro (ficha)' => route('encuentros.show', $this->A['encuentro']),
            'evento (ficha)' => route('eventos.show', $this->A['evento']),
            'ministerio (ficha)' => route('ministerios.show', $this->A['ministerio']),
            'persona (ficha)' => route('personas.show', $this->A['persona']),
            'sede (ficha)' => route('sedes.show', $this->A['sede']),
            'sin líder' => route('sin-lider.index'),
            'personas (listado)' => route('personas.index'),
            'líderes (listado)' => route('lideres.index'),
            'usuarios (listado)' => route('users.index'),
            'células (listado)' => route('celulas.index'),
            'eventos (listado)' => route('eventos.index'),
            'encuentros (listado)' => route('encuentros.index'),
            'ministerios (listado)' => route('ministerios.index'),
        ];
    }

    /**
     * Campos (atributo name) que cada pantalla de formulario envía hoy; la renovación no debe cambiarlos.
     *
     * @return array<string, array<int, string>>
     */
    protected function camposEsperados(): array
    {
        return [
            'persona (crear)' => ['nombres', 'apellidos', 'celular', 'email', 'fecha_nacimiento', 'estado', 'direccion', 'barrio', 'ciudad', 'lider_id', 'es_lider'],
            'célula (crear)' => ['nombre', 'sede', 'estado', 'lider_id', 'co_lider_id', 'dia_reunion', 'hora_reunion', 'lugar_reunion'],
            'encuentro (crear)' => ['nombre', 'fecha_realizacion', 'estado', 'personas[]'],
            'evento (crear)' => ['nombre', 'tipo', 'fecha', 'costo', 'estado', 'personas[]'],
            'ministerio (crear)' => ['nombre', 'estado', 'encargado_id', 'personas[]'],
            'usuario (crear)' => ['name', 'email', 'celular', 'role', 'persona_id'],
            'sede (crear)' => ['nombre', 'ciudad', 'direccion', 'telefono', 'email', 'pastor[nombres]', 'pastor[apellidos]', 'pastor[email]', 'pastor[password]', 'pastora[nombres]', 'pastora[apellidos]', 'pastora[email]', 'pastora[password]'],
            'traslado' => ['sede_destino_id'],
        ];
    }
}
