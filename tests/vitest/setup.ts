import { enableAutoUnmount } from '@vue/test-utils';
import { afterEach, vi } from 'vitest';

enableAutoUnmount(afterEach);

vi.mock('../../src/config.ts', () => ({ appName: 'bav' }));
vi.mock('@nextcloud/axios', () => ({ default: { post: vi.fn() } }));
vi.mock('../../src/toolkit/util/generate-url.ts', () => ({
  generateUrl: (path: string) => `/apps/bav/${path}`,
}));
vi.mock('../../src/toolkit/util/toasts.ts', () => ({ showError: vi.fn() }));
vi.mock('@nextcloud/l10n', () => ({
  translate: (_app: string, text: string, parameters: Record<string, string> = {}) =>
    text.replace(/\{(\w+)\}/g, (match, key: string) => parameters[key] ?? match),
}));
vi.mock('@nextcloud/vue', async () => import('./stubs.ts'));
vi.mock('@rotdrop/nextcloud-vue-components/lib/components/DynamicSvgIcon.vue', () => ({
  default: { template: '<span />' },
}));
