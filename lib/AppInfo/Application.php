<?php
/**
 * BAV - Bank Account Validator for German bank accounts.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Claus-Justus Heine 2014-2020, 2025, 2026
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

/*-********************************************************
 *
 * Bootstrap
 *
 */

use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\INavigationManager;
use OCP\IURLGenerator;
use OCP\IL10N;

use OCA\BAV\Listener\Registration as ListenerRegistration;
use OCA\BAV\Toolkit\AppInfo\AbstractApplication;
use OCA\BAV\Toolkit\Middleware\ExceptionMiddleware;

include_once __DIR__ . '/../Toolkit/AppInfo/AbstractApplication.php';

/**
 * App entry point.
 */
class Application extends AbstractApplication
{
  /** {@inheritdoc} */
  public function boot(IBootContext $context): void
  {
    $context->injectFn(
      function(
        IL10N $l,
        INavigationManager $navigationManager,
        IURLGenerator $urlGenerator,
      ) {
        $navigationManager->add(fn() => [
          'app' => self::$appName,
          'href' => '',
          'icon' => $urlGenerator->imagePath(self::$appName, 'app.svg'),
          'id' => self::$appName,
          'name' => 'BAV',
          // 'name' => $l->t('German Bank Account Validator'),
          'type' => 'action',
        ]);
      },
    );
  }

  /** {@inheritdoc} */
  public function register(IRegistrationContext $context):void
  {
    parent::register($context);
    ListenerRegistration::register($context);
    $context->registerMiddleWare(ExceptionMiddleware::class);
  }
}
