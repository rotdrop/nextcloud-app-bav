<?php
/**
 * ownCloud - bav
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Claus-Justus Heine <himself@claus-justus-heine.de>
 * @copyright Claus-Justus Heine 2014-2020
 */

namespace OCA\BAV\AppInfo;


use OCP\AppFramework\App;
use OCP\IL10N;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\IInitialStateService;
use OCP\IConfig;

class Application extends App implements IBootstrap {

    private $appName;
    
    public function __construct (array $urlParams=array()) {
        $infoXml = new \SimpleXMLElement(file_get_contents(__DIR__ . '/../../appinfo/info.xml'));
        $this->appName = (string)$infoXml->id;
        parent::__construct($this->appName, $urlParams);
    }

    // Called later than "register".
    public function boot(IBootContext $context): void
    {
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
        
        \malkusch\bav\ConfigurationRegistry::setConfiguration($bavConfig);
    }

    // Called earlier than boot, so anything initialized in the
    // "boot()" method must not be used here.
    public function register(IRegistrationContext $context): void
    {
        if ((@include_once __DIR__ . '/../../vendor/autoload.php')===false) {
            throw new \Exception('Cannot include autoload. Did you run install dependencies using composer?');
        }

        \OCP\Util::addScript($this->appName, 'app');
        \OCP\Util::addStyle($this->appName, 'app');    

        $config = \OC::$server->query(IConfig::class);
        $initialStateService = \OC::$server->query(IInitialStateService::class);

        $initialStateService->provideInitialState($this->appName, 'BAV', [ 'modal' => $config->getAppValue($this->appName, 'modal', true) ]);
    }
}

// Local Variables: ***
// c-basic-offset: 4 ***
// indent-tabs-mode: nil ***
// End: ***
