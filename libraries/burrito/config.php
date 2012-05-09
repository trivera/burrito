<?php 

defined('C5_EXECUTE') or die('Access Denied.');

class BurritoConfig {
	
	static public $_config = array();
	
	static public function loadConfig() {
		
		// cli
		if (defined('__C5__')) {
			$base = __C5__;
		}
		
		// concrete5
		elseif (defined('DIR_BASE')) {
			$base = DIR_BASE;
		}
		
		// base
		if (file_exists($_="{$base}/config/burrito/application.ini")) {
			self::$_config['app'] = parse_ini_file($_);
		}
		else {
			return false;
		}
		
		// extras
		if (file_exists($_="{$base}/config/burrito") && $handle=opendir($_)) {
			while (($f=readdir($handle)) !== false) {
				if (preg_match('/(?<!burrito)\.ini$/', $f)) {
					
					// merge each ini file in
					foreach (parse_ini_file("{$base}/config/burrito/{$f}", true) as $env => $conf) {
						
						// environment names are dynamic
						if (!array_key_exists($env, self::$_config)) {
							self::$_config[$env] = array();
						}
						
						// merge
						self::$_config[$env] = array_merge_recursive(
							self::$_config[$env],
							array(basename($f, '.ini') => $conf)
						);
					}
				}
			}
			closedir($handle);
		}
		else {
			throw new Exception("Burrito could not open {$_}");
		}
		
		// Burrito constants
		define('BURRITO_ENV', self::app('environment'));
	}
	
	static public function app() {
		$args = func_get_args();
		array_unshift($args, 'app');
		return call_user_func_array(array('self', 'get'), $args);
	}
	
	static public function env() {
		$args = func_get_args();
		array_unshift($args, BURRITO_ENV);
		return call_user_func_array(array('self', 'get'), $args);
	}
	
	static public function get() {
		
		$args = func_get_args();
		$value = self::$_config;
		
		foreach ($args as $key) {
			if (array_key_exists($key, $value)) {
				$value = $value[$key];
				continue;
			}
			return null;
		}
		
		return $value;
	}
	
	static public function includeDbConfig() {
		
		$map = array(
			'host' => 'DB_SERVER',
			'user' => 'DB_USERNAME',
			'pass' => 'DB_PASSWORD',
			'name' => 'DB_DATABASE',
		);
		
		foreach (self::env('db') as $key => $value) {
			define($map[$key], $value);
		}	
	}
}