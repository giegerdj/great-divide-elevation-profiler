<?php

define('APP_PATH', dirname(dirname(__FILE__)) . '/' );
require_once(APP_PATH . 'app/config/config.php');

// Add lib to include path.
$include_paths = array(
    APP_PATH . 'app/libraries/',
    TP_LIB_PATH,
    MODEL_PATH
);

set_include_path( implode(PATH_SEPARATOR, $include_paths) );


require_once(APP_PATH . 'app/libraries/Tachyon/Application.php');

/*
spl_autoload_register(function($class) {
    
    //we only wanto to autoload models for now
    
    $directories = array(MODEL_PATH);
    
    foreach($directories as $directory) {
        
        if( file_exists($directory . $class . '.php') ) {
            
            require_once($directory . $class . '.php');
            return;
        }
    }
    unset($directory);
});
*/

$urls = array(
    '/graph' => 'GraphController',
    '/graph/:startMile' => 'GraphController',
    '/graph/:startMile/:endMile' => 'GraphController',
    '404' => 'NotFoundController'
);

$app = new \Tachyon\Application($urls);
$app->setTemplateDir(VIEW_PATH)
    ->setControllerDir(CONTROLLER_PATH)
    ->run();
