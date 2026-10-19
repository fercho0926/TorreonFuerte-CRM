<?php

namespace Tests\Feature\Formularios;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class AvisosTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->armarDosSedes();
        $this->comoAdminEnSede($this->A['sede']);
    }

    public function test_el_exito_se_imprime_en_el_contenedor_de_avisos(): void
    {
        $html = $this->from(route('personas.create'))->followingRedirects()->post(route('personas.store'), [
            'nombres' => 'Aviso', 'apellidos' => 'Exito', 'celular' => '3105551111', 'estado' => 'visitante',
        ])->getContent();

        $this->assertStringContainsString('data-avisos', $html);
        $this->assertMatchesRegularExpression('/<div class="alert alert-success" role="status" data-aviso="exito">\s*Persona creada correctamente\./', $html);
    }

    public function test_el_error_permanece_visible_y_no_se_marca_como_aviso_que_se_cierra(): void
    {
        $this->A['liderUser']->forceFill(['celular' => null])->save();
        $html = $this->followingRedirects()->post(route('users.clave.store', $this->A['liderUser']))->getContent();

        $this->assertMatchesRegularExpression('/<div class="alert alert-danger" role="alert">\s*.* no tiene celular de WhatsApp/', $html);
        $this->assertStringNotContainsString('data-aviso="exito"', $html);
    }

    public function test_las_vistas_ya_no_repiten_el_bloque_de_mensajes(): void
    {
        $repetidas = [];
        foreach (\Illuminate\Support\Facades\File::allFiles(resource_path('views')) as $archivo) {
            $ruta = str_replace('\\', '/', $archivo->getRelativePathname());
            if ($ruta === 'layouts/admin.blade.php') {
                continue;
            }
            if (str_contains($archivo->getContents(), "session('success')")) {
                $repetidas[] = $ruta;
            }
        }

        $this->assertSame([], $repetidas, 'Vistas que repiten el mensaje de éxito: '.implode(', ', $repetidas));
    }
}
