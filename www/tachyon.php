<?php

define('APP_PATH', dirname(dirname(__FILE__)) . '/' );
require_once(APP_PATH . 'app/config/config.php');
require_once(APP_PATH . 'app/config/exceptions.php');

// Add lib to include path.
$include_paths = array(
    APP_PATH . 'app/libraries/',
    TP_LIB_PATH,
    MODEL_PATH,
    HELPER_PATH
);

set_include_path( implode(PATH_SEPARATOR, $include_paths) );


require_once(APP_PATH . 'app/libraries/Tachyon/Application.php');

$urls = array(
    '/segment-stats' => 'StatsController',
    '404' => 'NotFoundController'
);

$app = new \Tachyon\Application($urls);
$app->setTemplateDir(VIEW_PATH)
    ->setControllerDir(CONTROLLER_PATH)
    ->run();
