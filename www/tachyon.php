<?php

define('APP_PATH', dirname(dirname(__FILE__)) . '/' );

// Add lib to include path.
set_include_path( APP_PATH . 'app/libraries/');



define('VIEW_PATH', APP_PATH . 'app/views/');
define('CONTROLLER_PATH', APP_PATH . 'app/controllers/');

require_once(APP_PATH . 'app/config/config.php');
require_once(APP_PATH . 'app/libraries/Tachyon/Application.php');

$urls = array(
    "/graph" => "Graph",
    "404" => "NotFound"
);

$app = new \Tachyon\Application($urls);
$app->setTemplateDir(VIEW_PATH)
    ->setControllerDir(CONTROLLER_PATH)
    ->run();
