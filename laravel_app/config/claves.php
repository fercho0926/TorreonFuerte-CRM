<?php

/*
 * Claves temporales entregadas por WhatsApp (spec 005). Todos los valores se pueden ajustar con
 * variables de entorno; los de aquí son los valores por defecto.
 */

return [
    // Horas que dura una clave temporal antes de vencer.
    'vigencia_horas' => (int) env('CLAVES_VIGENCIA_HORAS', 24),

    // Longitud de la clave temporal (sin contar el guion de lectura).
    'longitud' => (int) env('CLAVES_LONGITUD', 10),

    // Solicitudes de «olvidé mi contraseña» permitidas por hora.
    'solicitudes_por_cuenta_hora' => (int) env('CLAVES_SOLICITUDES_POR_CUENTA_HORA', 3),
    'solicitudes_por_origen_hora' => (int) env('CLAVES_SOLICITUDES_POR_ORIGEN_HORA', 10),

    // Intentos fallidos de inicio de sesión por minuto (por correo y origen).
    'login_fallos_por_minuto' => (int) env('CLAVES_LOGIN_FALLOS_POR_MINUTO', 5),

    // Horas tras las cuales una solicitud sin atender se muestra como vencida.
    'solicitud_vence_horas' => (int) env('CLAVES_SOLICITUD_VENCE_HORAS', 48),

    // Longitud mínima de la contraseña definitiva.
    'contrasena_minimo' => (int) env('CLAVES_CONTRASENA_MINIMO', 8),
];
