<?php

namespace Tests\Feature\Claves;

use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ConSedes;
use Tests\Support\IniciaSesion;
use Tests\TestCase;

class PermisosClavesTest extends TestCase
{
    use ConSedes, IniciaSesion, RefreshDatabase;

    public function test_alcance_de_gestionar_claves(): void
    {
        $sedeA = $this->crearSede(['nombre' => 'A']);
        $sedeB = $this->crearSede(['nombre' => 'B']);
        $politica = new UserPolicy();

        $admin = $this->crearUsuario('admin');
        $pastor = $this->crearUsuario('pastor', null, $sedeA->id);
        $otroPastor = $this->crearUsuario('pastor', null, $sedeA->id);
        $liderA = $this->crearUsuario('lider_celula', null, $sedeA->id);
        $liderB = $this->crearUsuario('lider_celula', null, $sedeB->id);

        $this->assertTrue($politica->gestionarClaves($admin, $pastor));
        $this->assertTrue($politica->gestionarClaves($admin, $liderB));
        $this->assertTrue($politica->gestionarClaves($pastor, $liderA));
        $this->assertFalse($politica->gestionarClaves($pastor, $liderB), 'otra sede');
        $this->assertFalse($politica->gestionarClaves($pastor, $otroPastor), 'otro pastor');
        $this->assertFalse($politica->gestionarClaves($pastor, $admin), 'administrador');
        $this->assertFalse($politica->gestionarClaves($pastor, $pastor), 'a sí mismo');
        $this->assertFalse($politica->gestionarClaves($liderA, $liderA));
    }

    public function test_quien_puede_ver_solicitudes(): void
    {
        $sede = $this->crearSede();
        $politica = new UserPolicy();

        $this->assertTrue($politica->verSolicitudesClave($this->crearUsuario('admin')));
        $this->assertTrue($politica->verSolicitudesClave($this->crearUsuario('pastor', null, $sede->id)));
        $this->assertFalse($politica->verSolicitudesClave($this->crearUsuario('lider_celula', null, $sede->id)));
    }
}
