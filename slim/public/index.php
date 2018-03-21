<?php

if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

/*
 * ---------------------------------------------------------------
 * LOAD EXTERNAL SETTINGS
 * ---------------------------------------------------------------
 */

$settings_ini = '../settings.ini';
$external_settings = parse_ini_file( $settings_ini, true );
foreach( $external_settings as $prefix=>$section ) {
	$var_prefix = strtoupper($prefix);
	foreach( $section as $key=>$setting ) {
        $var = 'MY_' . $var_prefix . '_' . strtoupper($key);
		define( $var, $setting );
	}
}

/*
 * ---------------------------------------------------------------
 * LOAD REDBEAN
 * ---------------------------------------------------------------
 */

require __DIR__ . '/../redbean/rb-mysql.php';
R::setup(
    'mysql:host='. MY_DATABASE_HOSTNAME .';'.
    'dbname='. MY_DATABASE_DATABASE,
    MY_DATABASE_USERNAME, 
    MY_DATABASE_PASSWORD
); //for both mysql or mariaDB

/*
 * ---------------------------------------------------------------
 * CUSTOM VALIDATE CLASS
 * ---------------------------------------------------------------
 */
require __DIR__ . '/../src/validate.php';
// $validate = new validate();


/*
 * ---------------------------------------------------------------
 * START UP SLIM
 * ---------------------------------------------------------------
 */

require __DIR__ . '/../vendor/autoload.php';

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app
$app->run();
