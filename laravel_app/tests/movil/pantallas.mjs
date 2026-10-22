/**
 * Pantallas que recorre la auditoría móvil (las 38 del análisis, specs/008-experiencia-movil/analisis.md)
 * y el usuario de ejemplo con el que se inicia sesión (database/seeders/DatabaseSeeder.php: «admin@example.com»,
 * contraseña de ejemplo «password», solo entornos de desarrollo y de prueba).
 */
export const USUARIO = { email: 'admin@example.com', password: 'password' };

/** Identificadores del escenario sembrado: la persona 1 es miembro; la 3 es líder. */
const P = 1;
const LIDER = 3;

export const CON_SESION = [
    'dashboard',
    'personas', 'personas/create', `personas/${P}`, `personas/${P}/edit`, `personas/${P}/trasladar`,
    'lideres', 'lideres/designar', `lideres/${LIDER}`, `lideres/${LIDER}/quitar`,
    'celulas', 'celulas/create', 'celulas/1', 'celulas/1/edit',
    'encuentros', 'encuentros/create', 'encuentros/1', 'encuentros/1/edit',
    'eventos', 'eventos/create', 'eventos/1', 'eventos/1/edit',
    'ministerios', 'ministerios/create', 'ministerios/1', 'ministerios/1/edit',
    'sedes', 'sedes/create', 'sedes/1', 'sedes/1/edit', 'sedes/1/pastores',
    'sin-lider',
    'users', 'users/create', 'users/1/edit',
    'solicitudes-clave',
    'mi-cuenta', 'cambiar-clave',
];

/** Token fijo del QR del evento 1 en la base de prueba (lo asigna servidor.mjs; 32 caracteres como los reales). */
export const TOKEN_REGISTRO = 'RegistroMovilRegistroMovil000000';

/** Pantallas públicas (sin sesión). */
export const SIN_SESION = ['', 'olvide-clave', `registro/${TOKEN_REGISTRO}`];

export const ANCHOS = [320, 360, 390, 430];
