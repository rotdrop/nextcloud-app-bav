<?php
/**
 * BAV - Bank Account Validator for German bank accounts.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Claus-Justus Heine 2025
 * @license   AGPL-3.0-or-later
 *
 * Nextcloud DokuWiki is free software: you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * Nextcloud DokuWiki is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with Nextcloud DokuWiki. If not, see
 * <http://www.gnu.org/licenses/>.
 */

namespace OCA\BAV\BackgroundJob;

use Throwable;

use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

use OCA\BAV\Service\BAV;

/**
 * Update the bank id database from the Deutsche Bundesbank
 */
class UpdateJob extends TimedJob
{
  use \OCA\BAV\Toolkit\Traits\LoggerTrait;

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    string $appName,
    IConfig $cloudConfig,
    ITimeFactory $timeFactory,
    protected BAV $bav,
    protected LoggerInterface $logger,
  ) {
    parent::__construct($timeFactory);
    $this->setInterval($cloudConfig->getAppValue($appName, 'update_interval', 24*3600));
    $this->setAllowParallelRuns(false);
  }
  // phpcs:enable

  /** {@inheritdoc} */
  protected function run($argument)
  {
    try {
      if ($this->bav->isOutdated()) {
        $this->logInfo('The current database needs updating.');
        $this->bav->update();
        $this->logInfo('The current database has been updated.');
      } else {
        $this->logInfo('The current database does not need updating.');
      }
    } catch (Throwable $t) {
      $this->logException($t);
    }
  }
}
