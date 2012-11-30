<?php defined('SYSPATH') OR die('No direct access allowed.');

$settings = array(
	'default' => array(
        'type' => 'mysql',
        'charset' => 'utf8',
        'connection' => array(
            'hostname' => 'localhost',
            'username' => '',
            'password' => '',
            'database' => '',
        ),
        'caching' => FALSE,
        'profiling' => FALSE,
    )
);

switch (Kohana::$environment) {
	case Kohana::DEVELOPMENT:
		$settings['default']['connection']['username'] = 'root';
        $settings['default']['connection']['password'] = '';
        $settings['default']['connection']['database'] = 'd3admin';
		break;
	case Kohana::TESTING:
        $settings['default']['connection']['username'] = 'root';
        $settings['default']['connection']['password'] = 'admin';
        $settings['default']['connection']['database'] = 'd3admin';
		break;
	case Kohana::PRODUCTION:
        $settings['default']['connection']['username'] = 'root';
        $settings['default']['connection']['password'] = 'admin';
        $settings['default']['connection']['database'] = 'd3admin';
		break;
	default:
		break;
}

return $settings;