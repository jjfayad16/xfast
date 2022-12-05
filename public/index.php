<?php

defined('BASE_URL')
	|| define('BASE_URL', substr($_SERVER['PHP_SELF'],0,strpos($_SERVER['PHP_SELF'],'public/index.php')-1));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
	
// Define path to imagens / js / css directory
defined('EXT_PATH')
    || define('EXT_PATH', substr($_SERVER['PHP_SELF'],0,-9));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
	APPLICATION_PATH . '/models/bo' . PATH_SEPARATOR
	. APPLICATION_PATH . '/models/orm' . PATH_SEPARATOR
	. APPLICATION_PATH . '/models/form' . PATH_SEPARATOR
	. APPLICATION_PATH . '/models/dao' . PATH_SEPARATOR,
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();