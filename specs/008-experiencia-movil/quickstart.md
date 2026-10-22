# Guía de Validación Rápida: Experiencia móvil

## Requisitos

- `npm install` (incluye las dependencias de desarrollo `@playwright/test` y `sharp`) y `npx playwright install chromium`.
- `npm run iconos` genera los iconos desde `resources/iconos/marca.svg`.
- `npm run build`.

## Automática

```bash
php artisan test                 # incluye tests/Feature/Movil (guardas estáticas)
npm test                         # Vitest
npm run test:movil               # Playwright a 320/360/390/430 px (ver contracts/prueba-movil.md)
```

Esperado: todo en verde; las pruebas existentes (496 PHPUnit, 76 Vitest) siguen pasando.

## Manual en un celular real (HTTPS o `localhost` por túnel)

1. Abrir el tablero: los **accesos rápidos** están arriba; el resumen por sede se ve como tarjetas.
2. Tocar el botón del menú, el selector de sede (administrador) y el menú de usuario: se aciertan a la primera.
3. Abrir **Personas**: filas en tarjeta con nombre, estado y celular; la primera fila se ve sin bajar. Tocar «Filtros» y filtrar por estado; tocar «Ver más» en una tarjeta.
4. Tocar «Llamar» y «WhatsApp»: se abren la llamada y el chat.
5. Crear una persona: el teclado de **celular** es numérico, el de **correo** muestra la arroba, y el botón «Guardar» sigue visible con el teclado abierto. Cortar la red, enviar: aviso claro y datos conservados; volver a conectar y reenviar.
6. Abrir una ficha de persona, de líder, de célula y de evento: sin desplazamiento lateral; las tablas se ven como tarjetas.
7. iPhone: tocar un campo no acerca la pantalla.
8. «Agregar a pantalla de inicio»: aparece el icono «Torreón» y se abre sin barra de direcciones.
9. Modo avión y abrir la aplicación: aparece «Sin conexión» con «Reintentar».
10. Escritorio: listados y tablas conservan su presentación ancha.

Referencias: [contratos](contracts/), [modelo](data-model.md), [investigación](research.md).
