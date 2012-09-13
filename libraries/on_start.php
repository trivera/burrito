<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

// config
Loader::library('burrito/config', 'burrito');
BurritoConfig::loadConfig();

// helpers
Loader::helper('flash', 'burrito');
Loader::helper('access', 'burrito');

// libraries
Loader::library('burrito/model', 'burrito');
Loader::library('burrito/controller', 'burrito');
Loader::library('burrito/block_controller', 'burrito');

// Replace the image feed background with a subtle texture
define('WHITE_LABEL_DASHBOARD_BACKGROUND_SRC', DIR_REL.'/packages/burrito/img/texture.png');

/* 
	Debugging function
	Dumps unlimited parameters, wraps in <pre>
*/
if (!function_exists('d')) {
	function d() {
		$args = func_get_args();
		echo '<pre>';
		foreach ($args as $var) {
			print_r($var);
			echo '<br />';
		}
		echo '</pre>';
		exit;
	}	
}