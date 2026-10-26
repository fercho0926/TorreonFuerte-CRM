<?php

namespace Tests\Feature\Interfaz;

use PHPUnit\Framework\TestCase;

/**
 * Guardián de terminología (spec 010): el texto visible habla de «miembros», nunca de «personas».
 * Solo se revisan textos (vistas, lang, menú, manifiesto y cadenas de app/); los identificadores
 * de código (`$persona`, `personas.index`, `persona_id`...) no cuentan, y las excepciones
 * justificadas viven en tests/Support/excepciones-terminologia.php.
 */
class TerminologiaMiembrosTest extends TestCase
{
    /** Palabra suelta «persona(s)»: no forma parte de un identificador, ruta, clase ni clave. */
    private const PALABRA = '/(?<![\w$>\\\\\/.\-\'"\[])[Pp]ersonas?(?![\w\-\[(:$]|\.[a-z_])/u';

    public function test_el_texto_visible_no_dice_persona(): void
    {
        $raiz = dirname(__DIR__, 3);
        $excepciones = require $raiz.'/tests/Support/excepciones-terminologia.php';
        $hallazgos = [];

        foreach ($this->archivos($raiz) as $archivo) {
            $relativo = str_replace('\\', '/', substr($archivo, strlen($raiz) + 1));
            $contenido = (string) file_get_contents($archivo);
            $textos = str_ends_with($archivo, '.blade.php') || ! str_ends_with($archivo, '.php')
                ? $contenido
                : $this->cadenasYComentarios($contenido);

            foreach (explode("\n", $textos) as $i => $linea) {
                if (! preg_match('/@(var|param|return|property)\b/', $linea) && preg_match(self::PALABRA, $linea) && ! $this->exceptuada($relativo, $linea, $excepciones)) {
                    $hallazgos[] = $relativo.':'.($i + 1).'  '.trim(mb_substr($linea, 0, 140));
                }
            }
        }

        $this->assertSame([], $hallazgos, "Texto visible con «persona(s)»; usa «miembro(s)»:\n".implode("\n", $hallazgos));
    }

    /** @return list<string> */
    private function archivos(string $raiz): array
    {
        $lista = [$raiz.'/config/menu.php', $raiz.'/public/manifest.webmanifest'];
        foreach (['resources/views', 'lang', 'app'] as $carpeta) {
            $iterador = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($raiz.'/'.$carpeta, \FilesystemIterator::SKIP_DOTS));
            foreach ($iterador as $f) {
                if ($f->isFile() && str_ends_with($f->getFilename(), '.php')) {
                    $lista[] = $f->getPathname();
                }
            }
        }

        return array_values(array_filter($lista, 'is_file'));
    }

    /** En PHP solo importan las cadenas entre comillas y los comentarios. */
    private function cadenasYComentarios(string $codigo): string
    {
        $salida = [];
        foreach (explode("\n", $codigo) as $linea) {
            if (preg_match('/^\s*(\*|\/\*|\/\/|#)/', $linea)) {
                $salida[] = $linea;
                continue;
            }
            preg_match_all('/\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|\/\/ .*$/', $linea, $m);
            $salida[] = implode(' ', $m[0]);
        }

        return implode("\n", $salida);
    }

    /** @param array<string, list<string>> $excepciones archivo (o '*') => fragmentos permitidos */
    private function exceptuada(string $archivo, string $linea, array $excepciones): bool
    {
        foreach (array_merge($excepciones['*'] ?? [], $excepciones[$archivo] ?? []) as $fragmento) {
            if (str_contains($linea, $fragmento)) {
                return true;
            }
        }

        return false;
    }
}
