<?php

namespace OCA\Bav\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;
use OCP\IL10N;

class PageController extends Controller {
    private $bav;

    /** @var IL10N */
    private $l;

	public function __construct($AppName, IRequest $request, IL10N $l){
		parent::__construct($AppName, $request);
        $this->l = $l;
        $this->bav = new \malkusch\bav\BAV;
	}

    /**
     * CAUTION: the @Stuff turn off security checks, for this page no admin is
     *          required and no CSRF check. If you don't know what CSRF is, read
     *          it up in the docs or you might create a security hole. This is
     *          basically the only required method to add this exemption, don't
     *          add it to any other method if you don't exactly know what it does
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {
        $params = array('dialog' => false,
                        'bankAccountIBAN' => '',
                        'bankAccountBIC' => '',
                        'bankAccountBankId' => '',
                        'bankAccountId' => '',
                        'bankAccountBankName' => '');
        return new TemplateResponse('bav', 'main', $params);  // templates/main.php
    }

    /********************************************************
     * Funktion zur Erstellung einer IBAN aus BLZ+Kontonr
     * Gilt nur fuer deutsche Konten
     ********************************************************/
    private static function makeIBAN($blz, $kontonr) {
        $blz8 = str_pad ( $blz, 8, "0", STR_PAD_RIGHT);
        $kontonr10 = str_pad ( $kontonr, 10, "0", STR_PAD_LEFT);
        $bban = $blz8 . $kontonr10;
        $pruefsumme = $bban . "131400";
        $modulo = (bcmod($pruefsumme,"97"));
        $pruefziffer =str_pad ( 98 - $modulo, 2, "0",STR_PAD_LEFT);
        $iban = "DE" . $pruefziffer . $bban;
        return $iban;
    }

    /**
     * CAUTION: the @Stuff turn off security checks, for this page no admin is
     *          required and no CSRF check. If you don't know what CSRF is, read
     *          it up in the docs or you might create a security hole. This is
     *          basically the only required method to add this exemption, don't
     *          add it to any other method if you don't exactly know what it does
     *
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function dialog() {
        $params = array('dialog' => true,
                        'bankAccountIBAN' => '',
                        'bankAccountBIC' => '',
                        'bankAccountBankId' => '',
                        'bankAccountId' => '',
                        'bankAccountBankName' => '');
        return new TemplateResponse('bav', 'main', $params, '');  // templates/main.php
    }

    /**
     * Simply method that posts back the payload of the request
     * @NoAdminRequired
     */
    public function validate($bankAccountIBAN, $bankAccountBIC, $bankAccountBankId, $bankAccountId)
    {
        $message = '';
        $suggestions = '';
        $nl = "\n";
        $anyInput = $bankAccountIBAN.$bankAccountBIC.$bankAccountBankId.$bankAccountId != '';
    
        $bav = new \malkusch\bav\BAV;

        $bankAccountBankName = '';
    
        if ($bankAccountIBAN != '') {
            $bankAccountIBAN = strtoupper($bankAccountIBAN);
            $iban = new \PHP_IBAN\IBAN($bankAccountIBAN);
            $bankAccountIBAN = $iban->MachineFormat();
      
            if (!$iban->Verify()) {
                $message .= $this->l->t('Failed to validate IBAN').$nl;
                $suggestions = array();
                foreach ($iban->MistranscriptionSuggestions() as $alternative) {
                    if ($iban->Verify($alternative)) {
                        $alternative = $iban->MachineFormat($alternative);
                        $alternative = $iban->HumanFormat($alternative);
                        $suggestions[] = $alternative;
                    }
                }
                $suggestions = implode(', ', $suggestions).$nl;
            } else {
                $accountId = $iban->Account();
                $bankId = $iban->Bank();
                if ($bankAccountBankId == '') {
                    $bankAccountBankId = $bankId;
                } else if ($bankAccountBankId != $bankId) {
                    $message .= $this->l->t("Bank-id %s from IBAN and submitted bank-id %s do not coincide",
                                            array($bankAccountBankId, $bankId)).$nl;
                }
                if ($bankAccountId == '') {
                    $bankAccountId  = $accountId;
                } else if ($bankAccountId != $accountId) {
                    $message .= $this->l->t("Account-id %s from IBAN and submitted account-id %s do not coincide",
                                            array($bankAccountId, $accountId)).$nl;
                }
            }
        }

        if ($bankAccountBankId != '') {
            $blz = $bankAccountBankId;
            if ($bav->isvalidBank($blz)) {
                $agency = $bav->getMainAgency($blz);
                $bavBIC = $agency->getBIC();
                $bankAccountBankName = $agency->getName().', '.$agency->getCity();
                if ($bankAccountBIC == '') {
                    $bankAccountBIC = $bavBIC;
                } else if ($bankAccountBIC != $bavBIC) {
                    $message .= $this->l->t("Computed BIC %s and submitted BIC %s do not coincide",
                                            array($bavBIC, $bankAccountBIC)).$nl;
                }
                if ($bankAccountId != '') {
                    $kto = $bankAccountId;
                    if ($bav->isValidAccount($kto)) {
                        $selfIBAN = self::makeIBAN($blz, $kto);
                        if ($bankAccountIBAN == '') {
                            $bankAccountIBAN = $selfIBAN;
                        } else if ($bankAccountIBAN != $selfIBAN) {
                            $message .= $this->l->t("Generated IBAN %s and submitted IBAN %s do not coincide",
                                                    array($selfIBAN, $bankAccountIBAN));
                        }
                    } else {
                        $message .= $this->l->t('The account number %s @ %s appears not to be a valid German bank account id.',
                                                array($kto, $blz)).$nl;
                    }
                }
            } else {
                $message .= $this->l->t('The bank-id %s does not seem to be a valid German bank id.',
                                        array($blz)).$nl;
            }
        }

        if ($message == '' && $anyInput) {
            $message = $this->l->t('No errors found, but use at your own risk.');
        }

        return array('bankAccountIBAN' => $bankAccountIBAN,
                     'bankAccountBIC' => $bankAccountBIC,
                     'bankAccountBankId' => $bankAccountBankId,
                     'bankAccountId' => $bankAccountId,
                     'bankAccountBankName' => $bankAccountBankName,
                     'message' => nl2br($message),
                     'suggestions' => nl2br($suggestions));
    }    
}

// Local Variables: ***
// c-basic-offset: 4 ***
// indent-tabs-mode: nil ***
// End: ***
