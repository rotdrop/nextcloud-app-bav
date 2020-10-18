<?php
/* BAV -- Bank Account Validator
 *
 * @author Claus-Justus Heine
 * @copyright 2020 Claus-Justus Heine <himself@claus-justus-heine.de>
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

script($appName, 'admin-settings');
style($appName, 'admin-settings');

?>

<div class="section">
  <h2><?php echo $l->t('Bank Account Validator') ?></h2>
  <form id="bav-admin-settings">
    <input type="checkbox" class="checkbox" name="modal" id="bav-modal" <?php echo ($modal ? ' checked="checked"' : ''); ?>/>
    <label for="bav-modal"><?php echo $l->t('Disable other navigation elements while dialog is open.');?></label>
    <br/>
    <span class="msg"></span>
  </form>
</div>
