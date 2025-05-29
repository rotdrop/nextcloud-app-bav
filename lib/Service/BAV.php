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

namespace OCA\BAV\Service;

use Exception;
use InvalidArgumentException;
use PDO;
use malkusch;

use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Silly name. However, initialize the BAV database backend and allow to
 * conveniently set the update plan.
 */
class BAV extends malkusch\bav\BAV
{
  use \OCA\BAV\Toolkit\Traits\LoggerTrait;

  public const UPDATE_PLAN_NONE = 'none';
  public const UPDATE_PLAN_LOGGER = 'logger';
  public const UPDATE_PLAN_AUTO = 'auto';

  public const UPDATE_PLANS = [
    self::UPDATE_PLAN_NONE,
    self::UPDATE_PLAN_LOGGER,
    self::UPDATE_PLAN_AUTO,
  ];

  // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
  public function __construct(
    protected IConfig $cloudConfig,
    protected LoggerInterface $logger,
  ) {
    try {
      $bavConfig = new malkusch\bav\DefaultConfiguration();

      $dbType = $cloudConfig->getSystemValue('dbtype', 'mysql');
      $dbHost = $cloudConfig->getSystemValue('dbhost', 'localhost');
      $dbName = $cloudConfig->getSystemValue('dbname', false);
      $dbUser = $cloudConfig->getSystemValue('dbuser', false);
      $dbPass = $cloudConfig->getSystemValue('dbpassword', false);

      $dbURI = $dbType . ':' . 'host=' . $dbHost . ';dbname=' . $dbName;

      $pdo = new PDO($dbURI, $dbUser, $dbPass);
      $bavConfig->setDataBackendContainer(new malkusch\bav\PDODataBackendContainer($pdo));

      malkusch\bav\ConfigurationRegistry::setConfiguration($bavConfig);

      $this->setUpdatePlan(self::UPDATE_PLAN_LOGGER);

      parent::__construct();
    } catch (Throwable $t) {
      $this->logException($t);

      throw new Exception('Unable to initialize the BAV database backend.', 0, $t);
    }
  }
  // phpcs:enable

  /**
   * Configure the update plan to use and return the old plan.
   *
   * @param null|string|malkusch\bav\UpdatePlan $plan
   *
   * @return null|malkusch\bav\UpdatePlan
   *
   * @throws InvalidArgumentException
   */
  public function setUpdatePlan(string|malkusch\bav\UpdatePlan $plan):?malkusch\bav\UpdatePlan
  {
    $configuration = malkusch\bav\ConfigurationRegistry::getConfiguration();
    $oldUpdatePlan = $configuration->getUpdatePlan();

    if (is_string($plan)) {
      switch ($plan) {
        case self::UPDATE_PLAN_NONE:
          $plan = new class($this->logger) extends malkusch\bav\UpdatePlan {
            use \OCA\BAV\Toolkit\Traits\LoggerTrait;

            // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
            public function __construct(protected LoggerInterface $logger)
            {
            }
            // phpcs:enable

            /** {@inheritdoc} */
            public function perform(malkusch\bav\Databackend $backend)
            {
            }
          };
          break;
        case self::UPDATE_PLAN_AUTO:
          if ($oldUpdatePlan instanceof malkusch\bav\AutomaticUpdatePlan) {
            $plan = $oldUpdatePlan;
          } else {
            $plan = new malkusch\bav\AutomaticUpdatePlan();
          }
          break;
        case self::UPDATE_PLAN_LOGGER:
          $plan = new class($this->logger) extends malkusch\bav\UpdatePlan {
            use \OCA\BAV\Toolkit\Traits\LoggerTrait;

            // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
            public function __construct(protected LoggerInterface $logger)
            {
            }
            // phpcs:enable

            /** {@inheritdoc} */
            public function perform(malkusch\bav\Databackend $backend)
            {
              $this->logDebug("BAV's bank data is outdated.");
            }
          };
          break;
        default:
          throw new InvalidArgumentException('Update plan must be one of {' . implode(', ', self::UPDATE_PLANS) . '} or and instance of ' . malkusch\bav\UpdatePlan::class . '.');
      }
    }

    $configuration->setUpdatePlan($plan);

    return $oldUpdatePlan;
  }

  /**
   * Return the update plan which currently is in use.
   *
   * @return malkusch\bav\UpdatePlan
   */
  public function getUpdatePlan():malkusch\bav\UpdatePlan
  {
    return malkusch\bav\ConfigurationRegistry::getConfiguration()->getUpdatePlan();
  }

  /**
   * @return bool Return the state of the current database.
   */
  public function isOutdated():bool
  {
    return malkusch\bav\ConfigurationRegistry::getConfiguration()->getUpdatePlan()->isOutdated($this->getDataBackend());
  }
}
