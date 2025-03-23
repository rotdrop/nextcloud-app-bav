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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;

/** Admin settings controller class. */
class AdminSettingsController extends Controller
{
  // phpcs:disable Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    $appName,
    IRequest $request,
    private IConfig $config,
    private IL10N $l10n
  ) {
    parent::__construct($appName, $request);
  }
  // phpcs:enable Squiz.Commenting.FunctionComment.Missing

  /**
   * @param string $parameter
   *
   * @param mixed $value
   *
   * @return DataResponse
   *
   * @AuthorizedAdminSetting(settings=OCA\BAV\Settings\Admin)
   */
  public function set(string $parameter, mixed $value):DataResponse
  {
    switch ($parameter) {
      case 'modal':
        $realValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);
        if ($realValue === null) {
          return new DataResponse(['message' => $this->l->t('Invalid value `%s\' for parameter `%s\'.', [$value, $parameter])], Http::STATUS_BAD_REQUEST);
        }
        $this->config->setAppValue($this->appName, $parameter, $value);
        return new DataResponse(['message' => $this->l->t('Parameter `%s\' set to `%s\'', [$parameter, $value])], Http::STATUS_OK);
      default:
        return new DataResponse(['message' => $this->l->t('Unknown parameter: %s', [$parameter])], Http::STATUS_BAD_REQUEST);
    }
  }

  /**
   * @param string $parameter
   *
   * @param mixed $defaultValue
   *
   * @return DataResponse
   *
   * @NoAdminRequired
   * @AuthorizedAdminSetting(settings=OCA\BAV\Settings\Admin)
   */
  public function get(string $parameter, mixed $defaultValue):DataResponse
  {
    switch ($parameter) {
      case 'modal':
        if ($defaultValue !== null) {
          $realDefault = filter_var($defaultValue, FILTER_VALIDATE_BOOLEAN, ['flags' => FILTER_NULL_ON_FAILURE]);
          if ($realDefault === null) {
            return new DataResponse(['message' => $this->l->t('Invalid default value `%s\' for parameter `%s\'.', [$value, $parameter])], Http::STATUS_BAD_REQUEST);
          }
        } else {
          $realDefault = true;
        }
        $value = $this->config->getAppValue($this->appName, $parameter, $defaultValue);
        return new DataResponse(['value' => $value ], Http::STATUS_OK);
      default:
        return new DataResponse(['message' => $this->l->t('Unknown parameter: %s', [$parameter])], Http::STATUS_BAD_REQUEST);
    }
  }
}
