<?php

namespace Database\Seeders;

use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Evento;
use App\Models\EventoPago;
use App\Models\EventoPersona;
use App\Models\Ministerio;
use App\Models\Persona;
use App\Models\Red;
use App\Models\Sede;
use App\Models\User;
use App\Models\Zona;
use App\Services\SedeContext;
use App\Services\SedeService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Siembra un escenario de ejemplo con TRES sedes (contraseña "password" para todos los
     * usuarios): un administrador del sistema y, por sede, pastor y pastora, un líder con su
     * célula, personas con y sin líder, un evento con una inscripción y un pago, un encuentro y
     * un ministerio. Solo para entornos de desarrollo.
     */
    public function run(): void
    {
        $contexto = app(SedeContext::class);
        $servicio = app(SedeService::class);

        // El administrador del sistema no pertenece a ninguna sede.
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrador', 'role' => 'admin', 'sede_id' => null, 'password' => 'password']
        );

        $sedes = [
            ['clave' => 'centro', 'nombre' => 'Torreón Fuerte Centro', 'ciudad' => 'Torreón', 'red' => 'Jóvenes', 'zona' => 'Centro'],
            ['clave' => 'norte', 'nombre' => 'Torreón Fuerte Norte', 'ciudad' => 'Gómez Palacio', 'red' => 'Matrimonios', 'zona' => 'Norte'],
            ['clave' => 'sur', 'nombre' => 'Torreón Fuerte Sur', 'ciudad' => 'Lerdo', 'red' => 'Mujeres', 'zona' => 'Sur'],
        ];

        foreach ($sedes as $i => $d) {
            $sede = Sede::where('nombre', $d['nombre'])->first() ?? $servicio->crear([
                'nombre' => $d['nombre'],
                'ciudad' => $d['ciudad'],
                'pastor' => ['nombres' => 'Pastor', 'apellidos' => ucfirst($d['clave']), 'email' => "pastor.{$d['clave']}@example.com", 'password' => 'password'],
                'pastora' => ['nombres' => 'Pastora', 'apellidos' => ucfirst($d['clave']), 'email' => "pastora.{$d['clave']}@example.com", 'password' => 'password'],
            ]);

            $contexto->conSede($sede->id, fn () => $this->datosDeLaSede($sede, $d, $i));
        }
    }

    /**
     * @param  array<string, string>  $d
     */
    private function datosDeLaSede(Sede $sede, array $d, int $indice): void
    {
        $red = Red::firstOrCreate(['sede_id' => $sede->id, 'clave' => strtolower($d['red'])], ['nombre' => $d['red']]);
        $zona = Zona::firstOrCreate(['sede_id' => $sede->id, 'clave' => strtolower($d['zona'])], ['nombre' => $d['zona']]);

        $lider = Persona::firstOrCreate(
            ['sede_id' => $sede->id, 'nombres' => 'Lider', 'apellidos' => ucfirst($d['clave'])],
            ['tipo_documento' => 'CC', 'numero_documento' => '10'.$indice.'0000001', 'celular' => '31'.$indice.'0000001', 'estado' => 'miembro_activo', 'es_lider' => true, 'red_id' => $red->id, 'zona_id' => $zona->id]
        );
        User::updateOrCreate(
            ['email' => "lider.{$d['clave']}@example.com"],
            ['name' => $lider->nombre_completo, 'role' => 'lider_celula', 'persona_id' => $lider->id, 'sede_id' => $sede->id, 'password' => 'password']
        );

        $miembro = Persona::firstOrCreate(
            ['sede_id' => $sede->id, 'nombres' => 'Miembro', 'apellidos' => ucfirst($d['clave'])],
            ['tipo_documento' => 'CC', 'numero_documento' => '10'.$indice.'0000002', 'celular' => '31'.$indice.'0000002', 'estado' => 'miembro_activo', 'lider_id' => $lider->id]
        );
        Persona::firstOrCreate(
            ['sede_id' => $sede->id, 'nombres' => 'Visitante', 'apellidos' => ucfirst($d['clave'])],
            ['celular' => '31'.$indice.'0000003', 'estado' => 'visitante']
        );

        $celula = Celula::firstOrCreate(
            ['sede_id' => $sede->id, 'nombre' => "Célula {$d['zona']}"],
            ['lider_id' => $lider->id, 'estado' => 'activa']
        );
        $celula->miembros()->syncWithoutDetaching([$miembro->id => ['fecha_ingreso' => now()->toDateString()]]);

        $evento = Evento::firstOrCreate(
            ['sede_id' => $sede->id, 'nombre' => "Cena {$d['zona']}"],
            ['tipo' => Evento::TIPOS[0], 'fecha' => now()->addWeeks(2)->toDateString(), 'costo' => 50000, 'estado' => 'planificado']
        );
        $inscripcion = EventoPersona::firstOrCreate(['sede_id' => $sede->id, 'evento_id' => $evento->id, 'persona_id' => $miembro->id]);
        EventoPago::firstOrCreate(['sede_id' => $sede->id, 'evento_persona_id' => $inscripcion->id], ['monto' => 20000, 'fecha_pago' => now()->toDateString()]);

        Encuentro::firstOrCreate(['sede_id' => $sede->id, 'nombre' => "Encuentro {$d['zona']}"], ['fecha_realizacion' => now()->addMonth()->toDateString(), 'estado' => 'planificado']);
        Ministerio::firstOrCreate(['sede_id' => $sede->id, 'nombre' => 'Alabanza'], ['estado' => 'activo', 'encargado_id' => $lider->id]);
    }
}
