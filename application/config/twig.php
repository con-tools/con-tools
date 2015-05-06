<?php defined('SYSPATH') or die('No direct script access.');

return [

	/**
	 * Twig Loader options
	 */
	'loader' => [
		'extension' => 'twig',  // Extension for Twig files
		'path'      => 'views', // Path within cascading filesystem for Twig files
	],

	/**
	 * Twig Environment options
	 *
	 * http://twig.sensiolabs.org/doc/api.html#environment-options
	 */
	'environment' => [
		'auto_reload'         => true, //(Kohana::$environment == Kohana::DEVELOPMENT),
		'autoescape'          => true,
		'base_template_class' => 'Twig_Template',
		'cache'               => APPPATH.'cache/twig',
		'charset'             => 'utf-8',
		'optimizations'       => -1,
		'strict_variables'    => true,
	],

	/**
	 * Custom functions, filters and tests
	 *
	 *     'functions' => array(
	 *         'my_method' => array('MyClass', 'my_method'),
	 *     ),
	 */
	'functions' => [],
	'filters' => [],
	'tests' => [],

];
