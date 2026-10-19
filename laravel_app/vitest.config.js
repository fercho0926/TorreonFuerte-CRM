import { defineConfig } from 'vitest/config';

// Pruebas unitarias de los módulos JavaScript de formularios (funciones puras y comportamiento con jsdom).
export default defineConfig({
    test: {
        environment: 'jsdom',
        include: ['resources/js/**/*.test.js'],
        globals: false,
    },
});
