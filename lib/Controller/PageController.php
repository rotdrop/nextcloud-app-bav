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

namespace OCA\BAV\Controller;

use malkusch;
use malkusch\bav\BAV;
use PHP_IBAN;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\Util;

/** Main entry point for web frontend. */
class PageController extends Controller
{
  use \OCA\BAV\Toolkit\Traits\AssetTrait;

  private const ASSET = 'bav';

  private BAV $bav;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    private IConfig $config,
    protected IL10N $l
  ) {
    parent::__construct($appName, $request);
    $this->bav = new malkusch\bav\BAV;
    $this->initializeAssets(__DIR__);
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing

  /**
   * @return TemplateResponse
   *
   * @NoAdminRequired
   * @NoCSRFRequired
   */
  public function index(): TemplateResponse
  {
    $modal = $this->config->getAppValue($this->appName, 'modal', true);

    Util::addScript($this->appName, $this->getJSAsset(self::ASSET)['asset']);
    Util::addStyle($this->appName, $this->getCSSAsset(self::ASSET)['asset']);

    $params = [
      'appName' => $this->appName,
      'dialog' => false,
      'modal' => $modal,
      'bankAccountIBAN' => '',
      'bankAccountBIC' => '',
      'bankAccountBankId' => '',
      'bankAccountId' => '',
      'bankAccountBankName' => '',
    ];

    return new TemplateResponse($this->appName, 'main', $params);  // templates/main.php
  }

  /**
   * Generate an IBAN from a given German bank-account id and a German
   * bank-id.
   *
   * @param string $blz
   *
   * @param string $kontonr
   *
   * @return string
   */
  private static function makeIBAN(string $blz, string $kontonr):string
  {
    $blz8 = str_pad($blz, 8, '0', STR_PAD_RIGHT);
    $kontonr10 = str_pad($kontonr, 10, '0', STR_PAD_LEFT);
    $bban = $blz8 . $kontonr10;
    $pruefsumme = $bban . '131400';
    $modulo = (bcmod($pruefsumme, '97'));
    $pruefziffer = str_pad(98 - $modulo, 2, '0', STR_PAD_LEFT);
    $iban = 'DE' . $pruefziffer . $bban;
    return $iban;
  }

  /**
   * @return TemplateResponse
   *
   * @NoAdminRequired
   */
  public function dialog():TemplateResponse
  {
    Util::addScript($this->appName, $this->getJSAsset(self::ASSET)['asset']);
    Util::addStyle($this->appName, $this->getCSSAsset(self::ASSET)['asset']);

    $params = [
      'appName' => $this->appName,
      'dialog' => true,
      'bankAccountIBAN' => '',
      'bankAccountBIC' => '',
      'bankAccountBankId' => '',
      'bankAccountId' => '',
      'bankAccountBankName' => '',
    ];
    return new TemplateResponse($this->appName, 'main', $params, '');
  }

  /**
   * Simply method that posts back the payload of the request after applying
   * normalizations.
   *
   * @param string $bankAccountIBAN
   *
   * @param string $bankAccountBIC
   *
   * @param string $bankAccountBankId
   *
   * @param string $bankAccountId
   *
   * @return array
   *
   * @NoAdminRequired
   */
  public function validate(
    string $bankAccountIBAN,
    string $bankAccountBIC,
    string $bankAccountBankId,
    string $bankAccountId,
  ):array {
    $message = '';
    $suggestions = '';
    $nl = "\n";
    $anyInput = $bankAccountIBAN.$bankAccountBIC.$bankAccountBankId.$bankAccountId != '';

    $bav = new malkusch\bav\BAV;

    $bankAccountBankName = '';

    if ($bankAccountIBAN != '') {
      $bankAccountIBAN = strtoupper($bankAccountIBAN);
      $iban = new PHP_IBAN\IBAN($bankAccountIBAN);
      $bankAccountIBAN = $iban->MachineFormat();

      if (!$iban->Verify()) {
        $message .= $this->l->t('Failed to validate IBAN').$nl;
        $suggestions = [];
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
        } elseif ($bankAccountBankId != $bankId) {
          $message .= $this->l->t(
            'Bank-id %s from IBAN and submitted bank-id %s do not coincide',
            [ $bankAccountBankId, $bankId ]
          ) . $nl;
        }
        if ($bankAccountId == '') {
          $bankAccountId  = $accountId;
        } elseif ($bankAccountId != $accountId) {
          $message .= $this->l->t(
            'Account-id %s from IBAN and submitted account-id %s do not coincide',
            [ $bankAccountId, $accountId ],
          ) . $nl;
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
        } elseif ($bankAccountBIC != $bavBIC) {
          $message .= $this->l->t(
            'Computed BIC %s and submitted BIC %s do not coincide',
            [ $bavBIC, $bankAccountBIC ],
          ) . $nl;
        }
        if ($bankAccountId != '') {
          $kto = $bankAccountId;
          if ($bav->isValidAccount($kto)) {
            $selfIBAN = self::makeIBAN($blz, $kto);
            if ($bankAccountIBAN == '') {
              $bankAccountIBAN = $selfIBAN;
            } elseif ($bankAccountIBAN != $selfIBAN) {
              $message .= $this->l->t(
                'Generated IBAN %s and submitted IBAN %s do not coincide',
                [ $selfIBAN, $bankAccountIBAN ],
              ) . $nl;
            }
          } else {
            $message .= $this->l->t(
              'The account number %s @ %s appears not to be a valid German bank account id.',
              [ $kto, $blz ],
            ) . $nl;
          }
        }
      } else {
        $message .= $this->l->t(
          'The bank-id %s does not seem to be a valid German bank id.', $blz) . $nl;
      }
    }

    if ($message == '' && $anyInput) {
      $message = $this->l->t('No errors found, but use at your own risk.');
    }

    return [
      'bankAccountIBAN' => $bankAccountIBAN,
      'bankAccountBIC' => $bankAccountBIC,
      'bankAccountBankId' => $bankAccountBankId,
      'bankAccountId' => $bankAccountId,
      'bankAccountBankName' => $bankAccountBankName,
      'message' => nl2br($message),
      'suggestions' => nl2br($suggestions),
    ];
  }
}
