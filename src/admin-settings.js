/**
 * BAV -- German Bank Account Validator
 *
 * @author Claus-Justus Heine
 * @copyright 2014-2021, 2025 Claus-Justus Heine <himself@claus-justus-heine.de>
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

// import { appName } from './config.ts';
import generateUrl from './toolkit/util/generate-url.ts';

const jQuery = require('jquery');
const $ = jQuery;

require('jquery-ui');
// require('jquery-ui/ui/effect');
require('nextcloud/jquery/requesttoken.js');

$(function() {
  $('#bav-modal').on('change', function(event) {
    const value = $(this).prop('checked');

    $.post(
      generateUrl('settings/admin/set/modal'), { value })
      .done(function(data) {
        console.info(data);
        $('#bav-admin-settings .msg').html(data.message);
        $('#bav-admin-settings .msg').show();
      })
      .fail(function(jqXHR) {
        const response = JSON.parse(jqXHR.responseText);
        console.log(response);
        if (response.message) {
          $('#bav-admin-settings .msg').html(response.message);
          $('#bav-admin-settings .msg').show();
        }
      });
  });
});
