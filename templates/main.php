<?php
/**
 * @var $_ array
 */
/**
 * @var $l OC_L10N
 */

if (!isset($_['dialog']) || !$_['dialog']) {
  \OCP\Util::addScript('bav', 'script');
  \OCP\Util::addStyle('bav', 'style');
}

?>
<form class="bank-account-validator">
  <table>
    <tr>
      <td class="label">
        <?php echo $l->t('IBAN'); ?>
      </td><td>
        <input class="bankAccountIBAN active"
               type="text"
               name="bankAccountIBAN"
               value="<?php $_['bankAccountIBAN']; ?>"
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
               value="<?php $_['bankAccountBIC']; ?>"
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
               value="<?php echo $_['bankAccountBankId']; ?>"
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
               value="<?php $_['bankAccountId']; ?>"
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
               value="<?php echo $_['bankAccountBankName']; ?>"
               placeholder="<?php echo $l->t("Bank Name"); ?>"
               disabled="disabled"
               />
      </td>
    </tr>
  </table>
  <div class="status"></div>
  <div class="suggestions"></div>
</form>
