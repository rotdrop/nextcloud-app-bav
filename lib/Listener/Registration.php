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

use OCP\AppFramework\Bootstrap\IRegistrationContext;

/**
 * Static listener registration class.
 */
class Registration
{
  /**
   * Register all listeners.
   *
   * @param IRegistrationContext $context
   *
   * @return void
   */
  public static function register(IRegistrationContext $context):void
  {
    self::registerListener($context, BeforeTemplateRenderedListener::class);
  }

  /**
   * @param IRegistrationContext $context
   *
   * @param string $class
   *
   * @return void
   */
  private static function registerListener(IRegistrationContext $context, string $class):void
  {
    $events = $class::EVENT;
    if (!is_array($events)) {
      $events = [ $events ];
    }
    foreach ($events as $event) {
      $context->registerEventListener($event, $class);
    }
  }
}
