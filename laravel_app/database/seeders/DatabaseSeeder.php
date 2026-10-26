<?php

namespace Database\Seeders;

use App\Models\Celula;
use App\Models\Encuentro;
use App\Models\Evento;
use App\Models\EventoPago;
use App\Models\EventoPersona;
use App\Models\Ministerio;
use App\Models\Persona;
use App\Models\Sede;
use App\Models\User;
use App\Services\SedeContext;
use App\Services\SedeService;
use App\Support\Celular;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Siembra un escenario de ejemplo con TRES sedes (contraseña "password" para todos los
     * usuarios): un administrador del sistema y, por sede, pastor y pastora, un líder con su
     * célula, miembros con y sin líder, un evento con una inscripción y un pago, un encuentro y
     * un ministerio. Solo para entornos de desarrollo.
     */
    public function run(): void
    {
        $contexto = app(SedeContext::class);
        $servicio = app(SedeService::class);

        $sedes = [
            ['clave' => 'centro', 'nombre' => 'Torreón Fuerte Centro', 'ciudad' => 'Torreón', 'lugar' => 'Centro'],
            ['clave' => 'norte', 'nombre' => 'Torreón Fuerte Norte', 'ciudad' => 'Gómez Palacio', 'lugar' => 'Norte'],
            ['clave' => 'sur', 'nombre' => 'Torreón Fuerte Sur', 'ciudad' => 'Lerdo', 'lugar' => 'Sur'],
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

        // El administrador ve todas las sedes, pero como toda cuenta es de un miembro (spec 016),
        // su miembro pertenece a la primera sede.
        $centro = Sede::where('nombre', $sedes[0]['nombre'])->firstOrFail();
        $miembroAdmin = $contexto->conSede($centro->id, fn () => Persona::firstOrCreate(
            ['sede_id' => $centro->id, 'nombres' => 'Administrador', 'apellidos' => 'Del Sistema'],
            ['celular' => '3000000000', 'email' => 'admin@example.com', 'estado' => 'miembro_activo', 'lider_id' => $centro->pastor_persona_id]
        ));
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['role' => 'admin', 'sede_id' => null, 'persona_id' => $miembroAdmin->id, 'password' => 'password']
        );

        // Sin eventos de modelo no se calcula la clave del celular: se completa aquí.
        Persona::withoutGlobalScopes()->whereNull('celular_clave')->whereNotNull('celular')->get()
            ->each(fn (Persona $p) => Persona::withoutGlobalScopes()->whereKey($p->id)->update(['celular_clave' => Celular::clave($p->celular)]));
    }

    /**
     * @param  array<string, string>  $d
     */
    private function datosDeLaSede(Sede $sede, array $d, int $indice): void
    {
        // Todo líder depende del pastor de la sede, que es la cabeza (spec 016).
        $lider = Persona::firstOrCreate(
            ['sede_id' => $sede->id, 'nombres' => 'Lider', 'apellidos' => ucfirst($d['clave'])],
            ['tipo_documento' => 'CC', 'numero_documento' => '10'.$indice.'0000001', 'celular' => '31'.$indice.'0000001', 'estado' => 'miembro_activo', 'es_lider' => true, 'lider_id' => $sede->pastor_persona_id]
        );
        User::updateOrCreate(
            ['email' => "lider.{$d['clave']}@example.com"],
            ['role' => 'lider_celula', 'persona_id' => $lider->id, 'sede_id' => $sede->id, 'password' => 'password']
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
            ['sede_id' => $sede->id, 'nombre' => "Célula {$d['lugar']}"],
            ['lider_id' => $lider->id, 'estado' => 'activa']
        );
        $celula->miembros()->syncWithoutDetaching([$miembro->id => ['fecha_ingreso' => now()->toDateString()]]);

        $evento = Evento::firstOrCreate(
            ['sede_id' => $sede->id, 'nombre' => "Cena {$d['lugar']}"],
            ['tipo' => Evento::TIPOS[0], 'fecha' => now()->addWeeks(2)->toDateString(), 'costo' => 50000, 'estado' => 'planificado']
        );
        $inscripcion = EventoPersona::firstOrCreate(['sede_id' => $sede->id, 'evento_id' => $evento->id, 'persona_id' => $miembro->id]);
        EventoPago::firstOrCreate(['sede_id' => $sede->id, 'evento_persona_id' => $inscripcion->id], ['monto' => 20000, 'fecha_pago' => now()->toDateString()]);

        Encuentro::firstOrCreate(['sede_id' => $sede->id, 'nombre' => "Encuentro {$d['lugar']}"], ['fecha_realizacion' => now()->addMonth()->toDateString(), 'estado' => 'planificado']);
        Ministerio::firstOrCreate(['sede_id' => $sede->id, 'nombre' => 'Alabanza'], ['estado' => 'activo', 'encargado_id' => $lider->id]);
    }
}
