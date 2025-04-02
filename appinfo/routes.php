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
return [
  'routes' => [
    [
      'name' => 'page#index',
      'url' => '/',
      'verb' => 'GET',
    ],
    [
      'name' => 'validation#validate',
      'url' => '/validate',
      'verb' => 'POST',
    ],
    [
      'name' => 'admin_settings#set',
      'url' => '/settings/admin/set/{parameter}',
      'verb' => 'POST',
    ],
  ]
];
