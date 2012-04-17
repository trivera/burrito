<?php
defined('C5_EXECUTE') or die(_("Access Denied."));

/* 
	Misc global functions and things that happen
	during Concrete5's on_start event hook.
*/

// Load required libraries right away
Loader::library('burrito/model', 'burrito');
Loader::library('burrito/controller', 'burrito');
Loader::helper('flash', 'burrito');

// Replace the image feed background with a subtle texture
define('WHITE_LABEL_DASHBOARD_BACKGROUND_SRC', '/packages/burrito/img/texture.png');

/* 
	Debugging function
	Dumps unlimited parameters, wraps in <pre>
*/
if (!function_exists('d')) {
	function d() {
		echo '<pre>';
		foreach (func_get_args() as $var) {
			print_r($var);
			echo '<br />';
		}
		echo '</pre>';
		exit;
	}	
}