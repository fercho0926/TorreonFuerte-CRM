<?php

namespace Tests\Feature\RegistroQr;

use PHPUnit\Framework\Attributes\DataProvider;

class PanelTest extends RegistroQrTestCase
{
    private function rutaFicha(string $destino): string
    {
        return route(['evento' => 'eventos.show', 'encuentro' => 'encuentros.show', 'celula' => 'celulas.show'][$destino], $this->A[$destino]);
    }

    #[DataProvider('destinos')]
    public function test_la_ficha_muestra_el_qr_y_el_enlace(string $destino): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get($this->rutaFicha($destino))
            ->assertOk()
            ->assertSee('Registro por QR')
            ->assertSee('<svg', false)
            ->assertSee(route('registro.show', $this->token($destino)), false)
            ->assertSee(route('registro-qr.svg', [$destino, $this->A[$destino]->id]), false);
    }

    #[DataProvider('destinos')]
    public function test_la_descarga_entrega_un_svg(string $destino): void
    {
        $this->iniciarSesionComo($this->A['pastor']);

        $respuesta = $this->get(route('registro-qr.svg', [$destino, $this->A[$destino]->id]))->assertOk();

        $this->assertStringStartsWith('image/svg+xml', $respuesta->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $respuesta->headers->get('Content-Disposition'));
        $this->assertStringContainsString('<svg', $respuesta->getContent());
    }

    #[DataProvider('destinos')]
    public function test_abrir_cerrar_y_regenerar_cambian_el_estado_y_el_token(string $destino): void
    {
        $this->iniciarSesionComo($this->A['pastor']);
        $id = $this->A[$destino]->id;
        $viejo = $this->token($destino);

        $this->post(route('registro-qr.abrir', [$destino, $id]))->assertRedirect();
        $this->assertFalse((bool) $this->A[$destino]->fresh()->registro_abierto);

        $this->post(route('registro-qr.abrir', [$destino, $id]))->assertRedirect();
        $this->assertTrue((bool) $this->A[$destino]->fresh()->registro_abierto);

        $this->post(route('registro-qr.regenerar', [$destino, $id]))->assertRedirect();
        $this->assertNotSame($viejo, $this->token($destino));
        $this->assertSame(32, strlen($this->token($destino)));
    }

    public function test_el_lider_gestiona_solo_el_qr_de_su_celula(): void
    {
        $this->iniciarSesionComo($this->A['liderUser']);

        $this->get(route('registro-qr.svg', ['celula', $this->A['celula']->id]))->assertOk();
        $this->get(route('registro-qr.svg', ['evento', $this->A['evento']->id]))->assertForbidden();
        $this->post(route('registro-qr.regenerar', ['encuentro', $this->A['encuentro']->id]))->assertForbidden();
    }

    #[DataProvider('destinos')]
    public function test_un_destino_sin_token_lo_recibe_al_abrir_su_ficha(string $destino): void
    {
        // Filas creadas sin pasar por el modelo (seeders, importaciones).
        $tabla = $this->A[$destino]->getTable();
        \Illuminate\Support\Facades\DB::table($tabla)->where('id', $this->A[$destino]->id)->update(['registro_token' => null]);
        $this->iniciarSesionComo($this->A['pastor']);

        $this->get($this->rutaFicha($destino))->assertOk()->assertSee('Registro por QR');

        $this->assertSame(32, strlen($this->token($destino)));
        $this->get(route('registro.show', $this->token($destino)))->assertOk();
    }

    public function test_cada_destino_nuevo_recibe_su_token_unico(): void
    {
        $tokens = collect(['evento', 'encuentro', 'celula'])->map(fn ($d) => $this->token($d))
            ->merge([$this->token('evento', 'B'), $this->token('encuentro', 'B'), $this->token('celula', 'B')]);

        $this->assertCount(6, $tokens->unique());
        $tokens->each(fn ($t) => $this->assertSame(32, strlen($t)));
    }
}
