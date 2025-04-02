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
use OCP\IL10N;
use OCP\IRequest;
use OCP\Util;
use Psr\Log\LoggerInterface;

/**
 * Validate bank account data and complete it.
 *
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 */
class ValidationController extends Controller
{
  use \OCA\BAV\Toolkit\Traits\LoggerTrait;

  private BAV $bav;

  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IRequest $request,
    protected IL10N $l,
    protected LoggerInterface $logger,
  ) {
    parent::__construct($appName, $request);
    $this->bav = new malkusch\bav\BAV;
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing

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
   * Simply method that posts back the payload of the request after applying
   * normalizations.
   *
   * @param string $IBAN
   *
   * @param string $BIC
   *
   * @param string $bankId
   *
   * @param string $accountId
   *
   * @return DataResponse
   *
   * @NoAdminRequired
   */
  public function validate(
    string $IBAN,
    string $BIC,
    string $bankId,
    string $accountId,
  ):DataResponse {
    $messages = [];
    $suggestions = [];
    $alternatives = [];

    $bav = new malkusch\bav\BAV;

    $bankAccountIBAN = $IBAN;
    $bankAccountBIC = $BIC;
    $bankAccountBankId = $bankId;
    $bankAccountId = $accountId;

    $anyInput = $bankAccountIBAN.$bankAccountBIC.$bankAccountBankId.$bankAccountId != '';

    $bankAccountBankName = '';

    if ($bankAccountIBAN != '') {
      $bankAccountIBAN = strtoupper($bankAccountIBAN);
      $iban = new PHP_IBAN\IBAN($bankAccountIBAN);
      $bankAccountIBAN = $iban->MachineFormat();

      if (!$iban->Verify()) {
        $messages[] = $this->l->t('Failed to validate IBAN');
        // php-iban does recompute the checksum, so if just the checksum is
        // wrong it will possibly not find it.
        $accountId = $iban->Account();
        $bankId = $iban->Bank();
        $this->logInfo('BANK AND ACCOUNT ' . $bankId . ' ' . $accountId);
        $alternative = self::makeIBAN($bankId, $accountId);
        $alternatives = $iban->MistranscriptionSuggestions();
        array_unshift($alternatives, $alternative);
      } else {
        $accountId = $iban->Account();
        $bankId = $iban->Bank();
        if ($bankAccountBankId == '') {
          $bankAccountBankId = $bankId;
        } elseif ($bankAccountBankId != $bankId) {
          $messages[] = $this->l->t(
            'Bank-id %1$s from IBAN and submitted bank-id %2$s do not coincide',
            [ $bankId, $bankAccountBankId ]
          );
          $alternatives[] = self::makeIBAN($bankId, $accountId);
        }
        if ($bankAccountId == '') {
          $bankAccountId  = $accountId;
        } elseif ($bankAccountId != $accountId) {
          $messages[] = $this->l->t(
            'Account-id %1$s from IBAN and submitted account-id %2$s do not coincide',
            [ $accountId, $bankAccountId ],
          );
          $alternatives[] = self::makeIBAN($bankId, $accountId);
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
          $messages[] = $this->l->t(
            'Computed BIC %1$s and submitted BIC %2$s do not coincide',
            [ $bavBIC, $bankAccountBIC ],
          );
        }
        if ($bankAccountId != '') {
          $kto = $bankAccountId;
          if ($bav->isValidAccount($kto)) {
            $selfIBAN = self::makeIBAN($blz, $kto);
            if ($bankAccountIBAN == '') {
              $bankAccountIBAN = $selfIBAN;
            } elseif ($bankAccountIBAN != $selfIBAN) {
              $messages[] = $this->l->t(
                'Generated IBAN %1$s and submitted IBAN %2$s do not coincide',
                [ $selfIBAN, $bankAccountIBAN ],
              );
              $alternatives[] = $selfIBAN;
            }
          } else {
            $messages[] = $this->l->t(
              'The account number %1$s @ %2$s does not appear to be a valid German bank account id',
              [ $kto, $blz ],
            );
          }
        }
      } else {
        $messages[] = $this->l->t(
          'The bank-id %s does not seem to be a valid German bank id', $blz);
      }
    }

    if (!empty($alternatives)) {
      sort($alternatives);
      array_unique($alternatives);
      foreach ($alternatives as $alternative) {
        if ($iban->Verify($alternative)) {
          $suggestions[iban_to_machine_format($alternative)] = iban_to_human_format($alternative);
        }
      }
    }

    if (empty($messages) && $anyInput) {
      $messages[] = $this->l->t('No errors found, but use at your own risk');
    }

    return new DataResponse([
      'IBAN' => $bankAccountIBAN,
      'BIC' => $bankAccountBIC,
      'bankId' => $bankAccountBankId,
      'accountId' => $bankAccountId,
      'bankName' => $bankAccountBankName,
      'messages' => $messages,
      'suggestions' => $suggestions,
    ]);
  }
}
