/**
 * BAV -- German Bank Account Validator
 *
 * @author Claus-Justus Heine
 * @copyright 2014-2022, 2024-2026 Claus-Justus Heine <himself@claus-justus-heine.de>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 */

import type { App } from 'vue';

import { subscribe } from '@nextcloud/event-bus';
import { createApp } from 'vue';
import { appName } from './config.ts';

// https://stackoverflow.com/questions/69488256/vue-3-append-component-to-the-dom-best-practice

// type BavVueInstance = Component & {
//   getMounted: () => boolean;
//   getVisibility: () => boolean;
//   setVisibility: (visible: boolean) => void;
// };

let vueApp: App|undefined;

const mount = async (target: HTMLElement) => {
  if (!vueApp) {
    const vueComponent = (await import('./App.vue')).default;
    vueApp = createApp(vueComponent);
  }
  if (!vueApp?._instance?.isMounted) {
    return vueApp.mount(target);
  }
  if (!vueApp?._instance?.exposed?.getVisibility()) {
    vueApp?._instance?.exposed?.setVisibility(true);
  }
};

let mountTarget: HTMLDivElement;

subscribe('core:navigation:action', (action) => {
  if (action.id !== appName) {
    return;
  }
  if (!mountTarget) {
    mountTarget = document.createElement('div');
    mountTarget.id = `${appName}-mount-target`;
    document.body.appendChild(mountTarget);
  }
  mount(mountTarget);
});
