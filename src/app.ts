/**
 * BAV -- German Bank Account Validator
 *
 * @author Claus-Justus Heine
 * @copyright 2014-2022, 2024, 2025 Claus-Justus Heine <himself@claus-justus-heine.de>
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

import { appName } from './config.ts';
import onDocumentLoaded from './toolkit/util/on-document-loaded.ts';
import Vue from 'vue';
import { generateFilePath } from '@nextcloud/router';
import { getRequestToken } from '@nextcloud/auth';

// CSP config for webpack dynamic chunk loading
// eslint-disable-next-line
__webpack_nonce__ = btoa(getRequestToken() || '')

// eslint-disable-next-line
__webpack_public_path__ = generateFilePath(appName, '', '');

interface BavVueInstance extends Vue {
  getMounted: () => boolean,
  getVisibility: () => boolean,
  setVisibility: (visible: boolean) => void,
}

let vueInstance: BavVueInstance |undefined;

const mount = async (target: HTMLElement) => {
  if (!vueInstance) {
    const vueComponent = (await import('./App.vue')).default;
    vueInstance = new (Vue.extend(vueComponent))({
      // nothing ATM
    });
  }
  console.info('BAV VUE INSTANCE', {
    vueInstance,
    target,
  });
  if (!vueInstance.getMounted()) {
    return vueInstance.$mount(target);
  }
  if (!vueInstance.getVisibility()) {
    vueInstance.setVisibility(true);
  }
};

onDocumentLoaded(() => {
  const appLinkSelector = [
    'li.app-menu-entry a[href*="' + appName + '"]',
    'li.app-menu__overflow-entry a[href*="' + appName + '"]',
  ].map(selector => selector + ', ' + selector + ' *').join(', ');

  document.body.addEventListener('click', (event) => {
    const target = event?.target as HTMLElement|null;
    if (!target || !target.matches(appLinkSelector)) {
      console.info('NO BAV MATCH', event);
      return;
    }
    console.info('BAV GOT NAVIGATION CLICK EVENT', event);
    event.preventDefault();
    event.stopImmediatePropagation();
    const mountTarget = document.createElement('div');
    target.appendChild(mountTarget);
    mount(mountTarget);
  });
});
