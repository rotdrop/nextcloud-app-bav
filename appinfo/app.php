<?php
/**
 * Nextcloud - bav
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Claus-Justus Heine 2014-2020
 */

if ((@include_once __DIR__ . '/../vendor/autoload.php')===false) {
	throw new Exception('Cannot include autoload. Did you run install dependencies using composer?');
}

$ncConfig = \OC::$server->getSystemConfig();
$bavConfig = new \malkusch\bav\DefaultConfiguration();

$dbType = $ncConfig->getValue('dbtype', 'mysql');
$dbHost = $ncConfig->getValue('dbhost', 'localhost');
$dbName = $ncConfig->getValue('dbname', false);
$dbUser = $ncConfig->getValue('dbuser', false);
$dbPass = $ncConfig->getValue('dbpassword', false);

$dbURI = $dbType.':'.'host='.$dbHost.';dbname='.$dbName;

$pdo = new \PDO($dbURI, $dbUser, $dbPass);
$bavConfig->setDataBackendContainer(new \malkusch\bav\PDODataBackendContainer($pdo));

$bavConfig->setUpdatePlan(new \malkusch\bav\AutomaticUpdatePlan());

\malkusch\bav\ConfigurationRegistry::setConfiguration($bavConfig );

\OCP\Util::addScript('bav', 'script');
\OCP\Util::addStyle('bav', 'style');
