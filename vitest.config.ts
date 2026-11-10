import vue from '@vitejs/plugin-vue';
import { defineConfig } from 'vitest/config';

// Keep component tests independent of generated PHP types and production assets.
export default defineConfig({
  // Do not discover the parent Nextcloud server's tsconfig for test files.
  tsconfig: './tsconfig.json',
  plugins: [vue()],
  test: {
    environment: 'jsdom',
    include: ['tests/vitest/**/*.spec.ts'],
    setupFiles: ['tests/vitest/setup.ts'],
    clearMocks: true,
    restoreMocks: true,
  },
});
