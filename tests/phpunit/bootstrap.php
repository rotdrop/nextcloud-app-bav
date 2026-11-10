<?php

putenv('TEST_DONT_LOAD_APPS=1');
require_once __DIR__.'/../../../../tests/bootstrap.php';

\OC_App::loadApp('bav');

OC_Hook::clear();
