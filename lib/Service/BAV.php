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
use OCA\BAV\Scoped\malkusch\bav\BAV as BAVBackend;
use OCA\BAV\Scoped\malkusch\bav\Configuration\ConfigurationRegistry;
use OCA\BAV\Scoped\malkusch\bav\Configuration\DefaultConfiguration;
use OCA\BAV\Scoped\malkusch\bav\DataBackend\DataBackend;
use OCA\BAV\Scoped\malkusch\bav\DataBackend\Orm\Doctrine\DoctrineBackendContainer;
use OCA\BAV\Scoped\malkusch\bav\DataBackend\Update\AutomaticUpdatePlan;
use OCA\BAV\Scoped\malkusch\bav\DataBackend\Update\UpdatePlan;

use OCP\IConfig;
use Psr\Log\LoggerInterface;

/**
 * Silly name. However, initialize the BAV database backend and allow to
 * conveniently set the update plan.
 */
class BAV extends BAVBackend
{
  use \OCA\BAV\Scoped\OCA\RotDrop\Toolkit\Traits\LoggerTrait;

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
      $bavConfig = new DefaultConfiguration();

      $dbType = $cloudConfig->getSystemValue('dbtype', 'sqlite');
      $dbName = $cloudConfig->getSystemValue('dbname', 'nextcloud');
      $connectionParams = [
        'driver' => match ($dbType) {
          'mysql' => 'pdo_mysql',
          'pgsql' => 'pdo_pgsql',
          'sqlite', 'sqlite3' => 'pdo_sqlite',
          default => throw new InvalidArgumentException('Unsupported Nextcloud database type: ' . $dbType),
        },
        'user' => $cloudConfig->getSystemValue('dbuser', ''),
        'password' => $cloudConfig->getSystemValue('dbpassword', ''),
      ];

      if ($dbType === 'sqlite' || $dbType === 'sqlite3') {
        $dataDir = $cloudConfig->getSystemValue('datadirectory', \OC::$SERVERROOT . '/data');
        $connectionParams['path'] = $dataDir . '/' . $dbName . '.db';
      } else {
        $connectionParams['dbname'] = $dbName;
        $dbHost = $cloudConfig->getSystemValue('dbhost', 'localhost');
        $matches = [];
        if (preg_match('/^(.*):([^\]:]+)$/', $dbHost, $matches)) {
          $dbHost = $matches[1];
          if (is_numeric($matches[2])) {
            $connectionParams['port'] = (int)$matches[2];
          } else {
            $connectionParams['unix_socket'] = $matches[2];
          }
        }
        if (preg_match('/^\[([^\]]+)\]$/', $dbHost, $matches)) {
          $dbHost = $matches[1];
        }
        $connectionParams['host'] = $dbHost;
      }

      $bavConfig->setDataBackendContainer(DoctrineBackendContainer::buildByConnection($connectionParams));

      ConfigurationRegistry::setConfiguration($bavConfig);

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
   * @param null|string|UpdatePlan $plan
   *
   * @return null|UpdatePlan
   *
   * @throws InvalidArgumentException
   */
  public function setUpdatePlan(string|UpdatePlan $plan):?UpdatePlan
  {
    $configuration = ConfigurationRegistry::getConfiguration();
    $oldUpdatePlan = $configuration->getUpdatePlan();

    if (is_string($plan)) {
      switch ($plan) {
        case self::UPDATE_PLAN_NONE:
          $plan = new class($this->logger) extends UpdatePlan {
            use \OCA\BAV\Scoped\OCA\RotDrop\Toolkit\Traits\LoggerTrait;

            // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
            public function __construct(protected LoggerInterface $logger)
            {
            }
            // phpcs:enable

            /** {@inheritdoc} */
            public function perform(DataBackend $backend)
            {
            }
          };
          break;
        case self::UPDATE_PLAN_AUTO:
          if ($oldUpdatePlan instanceof AutomaticUpdatePlan) {
            $plan = $oldUpdatePlan;
          } else {
            $plan = new AutomaticUpdatePlan();
          }
          break;
        case self::UPDATE_PLAN_LOGGER:
          $plan = new class($this->logger) extends UpdatePlan {
            use \OCA\BAV\Scoped\OCA\RotDrop\Toolkit\Traits\LoggerTrait;

            // phpcs:ignore Squiz.Commenting.FunctionComment.Missing
            public function __construct(protected LoggerInterface $logger)
            {
            }
            // phpcs:enable

            /** {@inheritdoc} */
            public function perform(DataBackend $backend)
            {
              $this->logDebug("BAV's bank data is outdated.");
            }
          };
          break;
        default:
          throw new InvalidArgumentException('Update plan must be one of {' . implode(', ', self::UPDATE_PLANS) . '} or and instance of ' . UpdatePlan::class . '.');
      }
    }

    $configuration->setUpdatePlan($plan);

    return $oldUpdatePlan;
  }

  /**
   * Return the update plan which currently is in use.
   *
   * @return UpdatePlan
   */
  public function getUpdatePlan():UpdatePlan
  {
    return ConfigurationRegistry::getConfiguration()->getUpdatePlan();
  }

  /**
   * @return bool Return the state of the current database.
   */
  public function isOutdated():bool
  {
    return ConfigurationRegistry::getConfiguration()->getUpdatePlan()->isOutdated($this->getDataBackend());
  }
}
