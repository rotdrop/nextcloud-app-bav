<?php
/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Bav\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
        [ 'name' => 'page#index', 'url' => '/', 'verb' => 'GET' ],
        [ 'name' => 'page#dialog', 'url' => '/bav', 'verb' => 'POST' ],
        [ 'name' => 'page#validate', 'url' => '/validate', 'verb' => 'POST' ],
        [ 'name' => 'admin_settings#set', 'url' => '/settings/admin/set/{parameter}', 'verb' => 'POST' ],
        [
            'name' => 'admin_settings#get',
            'url' => '/settings/admin/get/{parameter}/{default}',
            'verb' => 'GET'
        ],
    ]
];
