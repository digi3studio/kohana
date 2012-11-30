<?php defined('SYSPATH') or die('No direct script access.');

$settings_base = array(
	'site'					=> array(
									'title'		=>'test',
									),
	'age_verify' 			=> FALSE,
	'auto_load_static_file' => TRUE,
	'city' 					=> array(
									'available' => 'asia|hk|sg|kr|cn|sh|st',
									'default' 	=> 'asia',
									),
	'language' 				=> array(
									'available' => 'en|sc|tc',
									'default' 	=> 'en',
									),
);

return $settings_base;