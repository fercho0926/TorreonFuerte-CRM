<?php

namespace Tests\Feature\Formularios;

use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class ConfirmacionesTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_ninguna_vista_usa_el_cuadro_de_confirmacion_del_navegador(): void
    {
        $encontradas = [];
        foreach (File::allFiles(resource_path('views')) as $archivo) {
            if (str_contains($archivo->getContents(), 'return confirm(') || str_contains($archivo->getContents(), 'onclick="confirm(')) {
                $encontradas[] = $archivo->getRelativePathname();
            }
        }

        $this->assertSame([], $encontradas, 'Vistas con confirm() nativo: '.implode(', ', $encontradas));
    }

    public function test_las_acciones_delicadas_declaran_el_dialogo_propio(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $paginas = [
            route('personas.show', $this->A['persona']) => ['¿Inactivar', '¿Eliminar'],
            route('personas.index') => ['¿Inactivar'],
            route('celulas.show', $this->A['celula']) => ['¿Quitar a esta persona de la célula?'],
            route('celulas.index') => ['¿Eliminar esta célula?'],
            route('eventos.index') => ['¿Eliminar este evento?'],
            route('eventos.show', $this->A['evento']) => ['¿Eliminar este abono?'],
            route('encuentros.index') => ['¿Eliminar este encuentro?'],
            route('ministerios.index') => ['¿Eliminar este ministerio?'],
            route('users.index') => ['¿Eliminar usuario?'],
            route('sedes.show', $this->A['sede']) => ['¿Desactivar esta sede?'],
        ];

        foreach ($paginas as $url => $titulos) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertStringContainsString('data-confirm="1"', $html, "{$url}: sin diálogo de confirmación");
            $this->assertStringContainsString('data-confirm-mensaje="', $html);
            foreach ($titulos as $titulo) {
                $this->assertStringContainsString($titulo, $html, "{$url}: falta el título {$titulo}");
            }
        }
    }

    public function test_el_dialogo_incluye_el_detalle_de_las_consecuencias(): void
    {
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);

        $html = $this->get(route('eventos.show', $this->A['evento']))->getContent();

        $this->assertStringContainsString('Se borrará el abono de $10,000.00', $html);
        $this->assertStringContainsString('data-confirm-peligro="1"', $html);
    }

    public function test_el_servidor_mantiene_sus_reglas_aunque_se_omita_la_confirmacion(): void
    {
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['liderUser']);

        // Un líder sin permiso recibe 403 al llamar la acción directamente.
        $this->delete(route('personas.destroy', $this->A['persona']))->assertForbidden();

        $this->comoAdminEnSede($this->A['sede']);
        // Con historial, la eliminación sigue respondiendo 422.
        $this->delete(route('personas.destroy', $this->A['lider']))->assertStatus(422);
        $this->assertNotNull(Persona::withoutGlobalScopes()->find($this->A['lider']->id));
    }
}
