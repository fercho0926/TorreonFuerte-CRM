<?php

namespace App\Services;

use App\Models\RegistroClave;
use App\Models\SolicitudClave;
use App\Models\User;
use App\Support\Celular;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Claves temporales entregadas por WhatsApp de forma asistida.
 *
 * La clave temporal ES la contraseña de la cuenta (solo su hash vive en users.password), marcada
 * como «debe cambiarla» y con vencimiento. La clave en claro se devuelve una única vez a quien la
 * genera y nunca se guarda en sesión, base de datos, registros ni caché.
 */
class ClavesTemporales
{
    /** Alfabeto sin caracteres ambiguos (sin 0, O, 1, I, l). */
    private const ALFABETO = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * Genera una clave temporal nueva para la cuenta, invalidando la anterior.
     *
     * @return array{clave: string, registro: RegistroClave}  la clave viene con guion de lectura (ABCDE-FGHJK)
     *
     * @throws ValidationException si la cuenta no tiene celular de WhatsApp
     */
    public function generar(User $objetivo, string $motivo, User $preparadaPor, ?SolicitudClave $solicitud = null, ?User $solicitadaPor = null): array
    {
        if (Celular::limpio($objetivo->celular) === null) {
            throw ValidationException::withMessages([
                'celular' => 'Esta cuenta no tiene celular de WhatsApp. Edita el usuario y completa el celular para poder enviarle la clave.',
            ]);
        }

        $clave = $this->crearClave();
        $vence = now()->addHours(config('claves.vigencia_horas'));

        $registro = DB::transaction(function () use ($objetivo, $motivo, $preparadaPor, $solicitud, $solicitadaPor, $clave, $vence) {
            // Cualquier clave temporal anterior sin usar queda reemplazada.
            RegistroClave::withoutGlobalScopes()
                ->where('user_id', $objetivo->id)
                ->whereIn('estado', [RegistroClave::PENDIENTE_ENVIO, RegistroClave::ENVIADA])
                ->update(['estado' => RegistroClave::REEMPLAZADA, 'updated_at' => now()]);

            // El cast «hashed» guarda solo el hash; esto invalida la contraseña o clave anterior.
            $objetivo->forceFill([
                'password' => $this->sinGuion($clave),
                'debe_cambiar_clave' => true,
                'clave_vence_en' => $vence,
            ])->save();

            $registro = new RegistroClave([
                'user_id' => $objetivo->id,
                'sede_id' => $objetivo->sede_id,
                'solicitud_id' => $solicitud?->id,
                'motivo' => $motivo,
                'solicitada_por' => $solicitadaPor?->id,
                'preparada_por' => $preparadaPor->id,
                'estado' => RegistroClave::PENDIENTE_ENVIO,
                'vence_en' => $vence,
            ]);
            $registro->save();

            return $registro;
        });

        return ['clave' => $clave, 'registro' => $registro];
    }

    /** Mensaje de WhatsApp listo para enviar (español, sin enlaces con la clave incrustada). */
    public function mensaje(User $usuario, string $clave): string
    {
        $sede = $usuario->sede()->value('nombre');
        $horas = (int) config('claves.vigencia_horas');
        $donde = $sede ? ' de la iglesia '.$sede : '';

        return "Hola {$usuario->name}, te damos acceso a ".config('app.sistema')."{$donde}.\n"
            ."Correo: {$usuario->email}\n"
            ."Clave temporal: {$clave}\n"
            ."Vale por {$horas} horas; al entrar te pediremos elegir tu propia contraseña.\n"
            .'Ingresa desde la dirección habitual del sistema.';
    }

    /** Enlace wa.me con el mensaje listo para el celular de la cuenta. */
    public function enlaceWhatsapp(User $usuario, string $mensaje): string
    {
        return 'https://wa.me/'.Celular::whatsapp($usuario->celular).'?text='.rawurlencode($mensaje);
    }

    /** Quita el guion de lectura y espacios, y pasa a mayúsculas. */
    public function normalizar(string $clave): string
    {
        return strtoupper(preg_replace('/[\s-]+/', '', $clave));
    }

    private function crearClave(): string
    {
        $longitud = max(6, (int) config('claves.longitud'));
        $clave = '';

        for ($i = 0; $i < $longitud; $i++) {
            $clave .= self::ALFABETO[random_int(0, strlen(self::ALFABETO) - 1)];
        }

        // Dos grupos para leerla y escribirla mejor: ABCDE-FGHJK.
        $mitad = intdiv($longitud, 2);

        return substr($clave, 0, $mitad).'-'.substr($clave, $mitad);
    }

    private function sinGuion(string $clave): string
    {
        return str_replace('-', '', $clave);
    }
}
