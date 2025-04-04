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

namespace OCA\BAV\Listener;

use PDO;
use Throwable;
use malkusch;

use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\IAppContainer;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Util;
use Psr\Log\LoggerInterface;

/**
 * Load the script assets for interactive requests.
 */
class BeforeTemplateRenderedListener implements IEventListener
{
  use \OCA\BAV\Toolkit\Traits\AssetTrait;

  public const EVENT = BeforeTemplateRenderedEvent::class;

  private const ASSET_BASENAME = 'bav';

  protected LoggerInterface $logger;

  protected string $appName;

  /**
   * @param IAppContainer $appContainer
   */
  public function __construct(protected IAppContainer $appContainer)
  {
  }

  /** {@inheritdoc} */
  public function handle(Event $event):void
  {
    $eventClass = get_class($event);
    if ($eventClass != self::EVENT) {
      return;
    }

    if ($event->getResponse()->getRenderAs() !== TemplateResponse::RENDER_AS_USER) {
      return;
    }

    $userSession = $this->appContainer->get(IUserSession::class);
    $user = $userSession->getUser();
    if (!$user instanceof IUser) {
      return;
    }

    $this->appName = $this->appContainer->get('AppName');
    $this->logger = $this->appContainer->get(LoggerInterface::class);
    $cloudConfig = $this->appContainer->get(IConfig::class);

    try {
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

      $dbURI = $dbType . ':' . 'host=' . $dbHost . ';dbname=' . $dbName;

      $pdo = new PDO($dbURI, $dbUser, $dbPass);
      $bavConfig->setDataBackendContainer(new malkusch\bav\PDODataBackendContainer($pdo));

      $bavConfig->setUpdatePlan(new malkusch\bav\AutomaticUpdatePlan());

      malkusch\bav\ConfigurationRegistry::setConfiguration($bavConfig);
    } catch (Throwable $t) {
      $this->logException($t);
    }
  }
}
