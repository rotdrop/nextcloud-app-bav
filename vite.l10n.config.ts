/**
 * Wrapper around vite.config.ts for `make l10n-ignore`: the analysis build
 * must not use the app root as output directory.
 */

import type { ConfigEnv, UserConfig, UserConfigExport } from 'vite';
import { mergeConfig } from 'vite';
import appConfig from './vite.config.ts';

export default async (env: ConfigEnv): Promise<UserConfig> => {
  const config: UserConfigExport = appConfig;
  const resolved = await (typeof config === 'function' ? config(env) : config);
  return mergeConfig(resolved, { build: { outDir: 'build/l10n-ignore-analysis' } });
};
