import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';

// Keep component tests independent of generated PHP types and production assets.
export default defineConfig({
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    include: ['tests/vitest/**/*.spec.ts'],
    setupFiles: ['tests/vitest/setup.ts'],
    clearMocks: true,
    restoreMocks: true,
  },
});
