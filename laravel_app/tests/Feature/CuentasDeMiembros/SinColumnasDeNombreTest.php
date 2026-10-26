<?php

namespace Tests\Feature\CuentasDeMiembros;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Guarda de la spec 016: la cuenta no tiene nombre ni celular propios. Falla si el código vuelve a
 * consultar o escribir esas columnas en `users`.
 */
class SinColumnasDeNombreTest extends TestCase
{
    public function test_el_codigo_no_usa_las_columnas_antiguas_de_la_cuenta(): void
    {
        $prohibidos = [
            '/users\.(name|celular)\b/' => 'consulta users.name / users.celular',
            "/User::(create|forceCreate|updateOrCreate)\\([^;]*'(name|celular)'\\s*=>/s" => 'crea una cuenta con nombre o celular propios',
            "/\\\$(user|usuario|cuenta)->(forceFill|update|fill)\\(\\['(name|celular)'/" => 'escribe el nombre o el celular en la cuenta',
        ];
        $hallazgos = [];

        foreach ([app_path(), resource_path('views'), database_path('seeders')] as $carpeta) {
            foreach (File::allFiles($carpeta) as $archivo) {
                $contenido = $archivo->getContents();
                foreach ($prohibidos as $patron => $motivo) {
                    if (preg_match($patron, $contenido)) {
                        $hallazgos[] = $archivo->getRelativePathname().': '.$motivo;
                    }
                }
            }
        }

        $this->assertSame([], $hallazgos, 'El nombre y el celular son del miembro (spec 016).');
    }
}
