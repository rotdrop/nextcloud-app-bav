<?php
/**
 * BAV - Bank Account Validator for German bank accounts.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Claus-Justus Heine 2014-2020, 2025
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

// phpcs:disable PSR1.Files.SideEffects
// phpcs:ignore PSR1.Files.SideEffects

namespace OCA\BAV\AppInfo;

use malkusch;
use PDO;

/*-********************************************************
 *
 * Bootstrap
 *
 */

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IConfig;
use OCP\IInitialStateService;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\Util;
use Psr\Log\LoggerInterface;

/*
 *
 **********************************************************
 *
 */

include_once __DIR__ . '/../../vendor/autoload.php';

/**
 * App entry point.
 */
class Application extends App implements IBootstrap
{
  use \OCA\BAV\Toolkit\Traits\AppNameTrait;
  use \OCA\BAV\Toolkit\Traits\AssetTrait;
  use \OCA\BAV\Toolkit\Traits\ApiRequestTrait;

  private const ASSET_BASENAME = 'bav';

  /** {@inheritdoc} */
  public function __construct(array $urlParams = [])
  {
    $this->appName = $this->getAppInfoAppName(__DIR__);
    parent::__construct($this->appName, $urlParams);
  }

  /** {@inheritdoc} */
  public function boot(IBootContext $context): void
  {
    $context->injectFn([$this, 'initialize']);
  }

  /**
   * @param IConfig $cloudConfig
   *
   * @param IInitialStateService $initialStateService
   *
   * @param IL10N $l
   *
   * @param IRequest $request
   *
   * @param IUserSession $userSession
   *
   * @param LoggerInterface $logger
   *
   * @return void
   */
  public function initialize(
    IConfig $cloudConfig,
    IInitialStateService $initialStateService,
    IL10N $l,
    IRequest $request,
    IUserSession $userSession,
    LoggerInterface $logger,
  ):void {
    $user = $userSession->getUser();
    if (empty($user)) {
      return; // only for authenticated users
    }
    $this->logger = $logger;
    if ($this->isNonInteractiveRequest($request)) {
      return; // only in the web-frontend
    }

    $this->l = $l;

    $initialStateService->provideInitialState(
      $this->appName,
      'data',
      [ 'modal' => $cloudConfig->getAppValue($this->appName, 'modal', true) ]
    );

    $this->initializeAssets(__DIR__);
    list('asset' => $scriptAsset,) = $this->getJSAsset(self::ASSET_BASENAME);
    Util::addScript($this->appName, $scriptAsset);
    // No separate CSS asset available.
    // list('asset' => $scriptAsset,) = $this->getCSSAsset(self::ASSET_BASENAME);
    // Util::addStyle($this->appName, $scriptAsset);

    $bavConfig = new malkusch\bav\DefaultConfiguration();

    $dbType = $cloudConfig->getSystemValue('dbtype', 'mysql');
    $dbHost = $cloudConfig->getSystemValue('dbhost', 'localhost');
    $dbName = $cloudConfig->getSystemValue('dbname', false);
    $dbUser = $cloudConfig->getSystemValue('dbuser', false);
    $dbPass = $cloudConfig->getSystemValue('dbpassword', false);

    $dbURI = $dbType.':'.'host='.$dbHost.';dbname='.$dbName;

    $pdo = new PDO($dbURI, $dbUser, $dbPass);
    $bavConfig->setDataBackendContainer(new malkusch\bav\PDODataBackendContainer($pdo));

    $bavConfig->setUpdatePlan(new malkusch\bav\AutomaticUpdatePlan());

    \malkusch\bav\ConfigurationRegistry::setConfiguration($bavConfig);
  }

  /** {@inheritdoc} */
  public function register(IRegistrationContext $context):void
  {
    // nothing
  }
}
