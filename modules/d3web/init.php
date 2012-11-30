<?php defined('SYSPATH') or die('No direct script access.');
 
Route::set('home', '(<city>(_<language>))(/<controller>)(/<action>)(/<id>)(.<format>)',array(
  'city' => '('.Kohana::config(Kohana::$environment.'.city.available').')'
    ))
	->defaults(array(
		'city'		 => Kohana::config(Kohana::$environment.'.city.default'),
		'language'	 => 'en',
		'controller' => 'home',
		'action'     => 'index',
		'id'		 => '',
		'format'	 => 'php',
	));