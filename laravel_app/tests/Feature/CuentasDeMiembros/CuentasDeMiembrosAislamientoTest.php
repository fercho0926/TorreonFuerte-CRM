<?php

namespace Tests\Feature\CuentasDeMiembros;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\EscenarioDosSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

/** Principio II: la búsqueda por celular no revela datos de otra sede (spec 016, research D8). */
class CuentasDeMiembrosAislamientoTest extends TestCase
{
    use ConSedes, EscenarioDosSedes, IniciaSesion, RefreshDatabase;

    public function test_el_celular_de_otra_sede_solo_produce_un_aviso_generico(): void
    {
        $this->armarDosSedes();
        $this->B['persona']->update(['celular' => '3207778899']);
        $this->iniciarSesionComo($this->A['pastor']);

        $html = $this->get(route('users.create', ['celular' => '3207778899']))->assertOk()->getContent();

        $this->assertStringContainsString('Ese celular pertenece a un miembro de otra sede; solicita su traslado', $html);
        $this->assertStringNotContainsString('PersonaBeta', $html);
        $this->assertStringNotContainsString('Iglesia Beta', $html);
        $this->assertStringNotContainsString('name="email"', $html);
    }

    public function test_no_se_puede_vincular_a_un_miembro_de_otra_sede(): void
    {
        $this->armarDosSedes();
        $this->iniciarSesionComo($this->A['pastor']);

        $this->post(route('users.store'), [
            'celular' => $this->B['persona']->celular, 'persona_id' => $this->B['persona']->id,
            'email' => 'fuga@iglesia.co', 'role' => 'user',
        ])->assertSessionHasErrors('persona_id');

        $this->assertSame(0, User::withoutGlobalScopes()->where('email', 'fuga@iglesia.co')->count());
    }
}
