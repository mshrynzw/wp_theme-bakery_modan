import { defineConfig } from 'vitest/config';

export default defineConfig({
  test: {
    environment: 'jsdom',
    include: ['tests/unit/js/**/*.test.ts'],
    setupFiles: ['tests/unit/js/setup.ts'],
  },
});
