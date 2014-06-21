<?php

require_once('config-strings.php');

define('WEB_CACHE_PATH', '/resources/cache/');
define('ABS_WEB_CACHE_PATH', APP_PATH . 'www/resources/cache/');

define('TP_LIB_PATH', APP_PATH . 'app/third_party/');
define('MODEL_PATH', APP_PATH . 'app/models/');
define('VIEW_PATH', APP_PATH . 'app/views/');
define('CONTROLLER_PATH', APP_PATH . 'app/controllers/');
define('HELPER_PATH', APP_PATH . 'app/helpers/');

define('DB_ERRMODE', PDO::ERRMODE_EXCEPTION);

