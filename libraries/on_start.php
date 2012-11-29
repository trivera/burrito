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

/* 
	Force a trailing slash to URLs
	for SEO benefit.
	
	Comment out if you wish to disable.
*/
$frags = explode('?', $_SERVER['REQUEST_URI'], 2); 
if(!User::isLoggedIn() && preg_match(',^/(?!tools|.php|.jpg|.jpeg|.png|.gif),', $frags[0]) && $_SERVER['REQUEST_METHOD'] == 'GET' && empty($_SERVER['argv'])){
	$redirect = false;
	if(substr($frags[0], 0, 1) != '/'){
		$redirect = true;
		$frags[0] = '/'.$frags[0];
	}
	if(substr($frags[0], -1) != '/'){
		$redirect = true;
		$frags[0] = $frags[0].'/';
	}
	if($redirect){
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.BASE_URL.implode('?', $frags));
	}
}