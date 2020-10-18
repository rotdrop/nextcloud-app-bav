<?php
/* Bav -- Bank Account Validator
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

namespace OCA\Bav\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\IConfig;

class Admin implements ISettings {

  const TEMPLATE = "admin-settings";

  /** @var string */
  private $appName;
  
  /** @var IConfig */
  private $config;

  public function __construct($appName, IConfig $config) {
    $this->appName = $appName;
    $this->config = $config;
  }

  public function getForm() {
    return new TemplateResponse(
      $this->appName,
      self::TEMPLATE,
      [
        'appName' => $this->appName,
        'modal' => $this->config->getAppValue($this->appName, 'modal'),
      ]);
  }

  /**
   * @return string the section ID, e.g. 'sharing'
   * @since 9.1
   */
  public function getSection() {
    return $this->appName;
  }

  /**
   * @return int whether the form should be rather on the top or bottom of
   * the admin section. The forms are arranged in ascending order of the
   * priority values. It is required to return a value between 0 and 100.
   *
   * E.g.: 70
   * @since 9.1
   */
  public function getPriority() {
    // @@TODO could be made a configure option.
    return 50;
  }
}

// Local Variables: ***
// c-basic-offset: 2 ***
// indent-tabs-mode: nil ***
// End: ***
