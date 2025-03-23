<?php
/**
 * Orchestra member, musician and project management application.
 *
 * BAV -- Bank Account Validator
 *
 * @author Claus-Justus Heine
 * @copyright 2020-2025 Claus-Justus Heine <himself@claus-justus-heine.de>
 * @license AGPL-3.0-or-later
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
?>
<form class="bank-account-validator bav">
  <table>
    <tr>
      <td class="label">
        <?php echo $l->t('IBAN'); ?>
      </td><td>
        <input class="bankAccountIBAN active"
               type="text"
               name="bankAccountIBAN"
               value="<?php $bankAccountIBAN; ?>"
               placeholder="<?php echo $l->t("IBAN of bank account"); ?>" />
      </td>
    </tr>
    <tr>
      <td class="label">
        <?php echo $l->t('BIC'); ?>
      </td><td>
        <input class="bankAccountBIC active"
               type="text"
               name="bankAccountBIC"
               value="<?php $bankAccountBIC; ?>"
               placeholder="<?php echo $l->t("BIC of bank"); ?>" />
      </td>
    </tr>
    <tr>
      <td class="label">
        <?php echo $l->t('Bank Id'); ?>
      </td><td>
        <input class="bankAccountBankId active"
               type="text"
               name="bankAccountBankId"
               value="<?php echo $bankAccountBankId; ?>"
               placeholder="<?php echo $l->t("Bank id of bank account"); ?>" />
      </td>
    </tr>
    <tr>
      <td class="label">
        <?php echo $l->t('Account Id'); ?>
      </td><td>
        <input class="bankAccountId active"
               type="text"
               name="bankAccountId"
               value="<?php $bankAccountId; ?>"
               placeholder="<?php echo $l->t("Id of bank account"); ?>" />
      </td>
    </tr>
    <tr>
      <td>
        <?php echo $l->t('Bank'); ?>
      </td>
      <td>
        <input class="bankAccountBankName inactive"
               type="text"
               name="bankAccountBankName"
               value="<?php echo $bankAccountBankName; ?>"
               placeholder="<?php echo $l->t("Bank Name"); ?>"
               disabled="disabled"
               />
      </td>
    </tr>
  </table>
  <div class="bav-status">&nbsp;</div>
  <div class="bav-suggestions"></div>
</form>
