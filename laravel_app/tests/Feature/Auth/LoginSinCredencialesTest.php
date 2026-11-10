<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/** RF-003: la pantalla pública de login no debe mostrar ninguna credencial de acceso. */
class LoginSinCredencialesTest extends TestCase
{
    public function test_la_pantalla_de_login_no_muestra_credenciales_de_demostracion(): void
    {
        $contenido = $this->get(route('login'))->assertOk()->getContent();

        $this->assertStringNotContainsString('Demo:', $contenido);
        $this->assertStringNotContainsString('admin@example.com', $contenido);
        // No debe aparecer la contraseña de demostración como texto suelto (distinto del atributo type="password").
        $this->assertStringNotContainsString('/ password<', $contenido);
    }
}
