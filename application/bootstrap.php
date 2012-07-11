<?php defined('SYSPATH') or die('No direct script access.');

//DIGI3 Bootstrap setup
$domains = array(
  'development' => 'localhost',
  'testing' => 'digi3studio.com',
  'production' => 'moet-birthday.com',
);

/*auto select environment setting */
switch(PHP_SAPI){
  case 'cli':
    spl_autoload_register(array('Kohana', 'auto_load'));
    $options = CLI::options('environment');
    if(!array_key_exists('environment', $options)){
      print PHP_EOL.'Usage: php index.php --uri=cron/<action> --environment=<environment> [options]'.PHP_EOL;
      print PHP_EOL.'--environment        development, testing or production'.PHP_EOL.PHP_EOL;
      exit();
    }
    //create the server enviornment
    $options['environment'] = strtolower($options['environment']);
    $_SERVER['SERVER_NAME'] = $domains[$options['environment']];
    $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    break;
  default:
    break;
}

foreach($domains as $i => $j){
  if(strpos($_SERVER['SERVER_NAME'], $j) !== FALSE){
    Kohana::$environment = $i;
    break;
  }
}
/* end auto select enviroment setting */

//default settings
$settings = array(
	'base_url' => '/',
	'profiling' => TRUE,
	'caching' => TRUE,
	'errors' => FALSE,
	'index_file' => FALSE,
);

//override default settings
switch (Kohana::$environment) {
	case Kohana::DEVELOPMENT:
		$settings['base_url'] = '/www/moet-web/';
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		break;
	case Kohana::TESTING:
        $settings['base_url'] = '/preview/moet/birthday/web/';
		ini_set('display_errors', 1);
		error_reporting(E_ALL);
		break;
	case Kohana::PRODUCTION:
	default:
        $settings['base_url'] = '/';
		break;
}
//fixes for the override
//override the $settings, the register_globals conflict with the core(system/classes/kohana/core.php), init()
if (ini_get('register_globals')){
	ini_set("register_globals", 0);
	Kohana::$base_url = rtrim($settings['base_url'], '/').'/';//copied from core.php, if register_global, the core.php can't set the base_url correctly
}

//some server will have mb function set as ISO-8859-1 by default
mb_internal_encoding('UTF-8');

//DIGI3 Bootstrap setup end

//-- Environment setup --------------------------------------------------------

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Asia/Hong_Kong');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

//-- Configuration and initialization -----------------------------------------

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = $_SERVER['KOHANA_ENV'];
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
//DIGI3 move array to $settings
Kohana::init($settings);

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
//Kohana::$log->attach(new Kohana_Log_File(APPPATH.'logs'));
//DIGI3 update the log file following our format
Kohana::$log->attach(new Kohana_Log_File(Kohana::config(Kohana::$environment.'.logs.path')));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Kohana_Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	 'auth'       => MODPATH.'auth',       // Basic authentication
	// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	// 'database'   => MODPATH.'database',   // Database access
	// 'image'      => MODPATH.'image',      // Image manipulation
	// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'oauth'      => MODPATH.'oauth',      // OAuth authentication
	// 'pagination' => MODPATH.'pagination', // Paging of results
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
	'd3admin' => MODPATH.'d3admin',
	));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'welcome',
		'action'     => 'index',
	));

if ( ! defined('SUPPRESS_REQUEST'))
{
	/**
	 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
	 * If no source is specified, the URI will be automatically detected.
	 */
	echo Request::instance()
		->execute()
		->send_headers()
		->response;
}
