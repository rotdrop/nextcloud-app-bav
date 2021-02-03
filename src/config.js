/**
 * BAV -- German Bank Account Validator
 *
 * @author Claus-Justus Heine
 * @copyright 2014-2021 Claus-Justus Heine <himself@claus-justus-heine.de>
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

const appInfo = require('appinfo/info.xml');
const appName = appInfo.info.id[0];
const webRoot = OC.appswebroots[appName] + '/';

const initialState = OCP.InitialState.loadState(appName, 'data');

export {
  appInfo,
  appName,
  webRoot,
  initialState,
};

// Local Variables: ***
// js-indent-level: 2 ***
// indent-tabs-mode: nil ***
// End: ***
