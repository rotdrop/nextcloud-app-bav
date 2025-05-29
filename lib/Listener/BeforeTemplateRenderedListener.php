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

namespace OCA\BAV\Listener;

use Throwable;

use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\IAppContainer;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Util;
use Psr\Log\LoggerInterface;

use OCA\BAV\Service\BAV;

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

    try {
      $this->initializeAssets(__DIR__);
      list('asset' => $scriptAsset,) = $this->getJSAsset(self::ASSET_BASENAME);
      util::addScript($this->appName, $scriptAsset);

      /* $unused = */$this->appContainer->get(BAV::class);
    } catch (Throwable $t) {
      $this->logException($t);
    }
  }
}
