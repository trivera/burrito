<?php

// arguments
if (count($args) != 1) {
	echo $tty->error('burrito-inject expects an argument');
	exit;
}

// site.php
if (in_array($args[0], array('config'))) {
	_loadDb();
	_loadConcrete();
}

function _loadDb() {
	
	$config = BurritoConfig::env('db');
	
	$map = array(
		'host' => 'DB_SERVER',
		'user' => 'DB_USERNAME',
		'pass' => 'DB_PASSWORD',
		'name' => 'DB_DATABASE',
	);
	
	foreach ($config as $key => $value) {
		if (array_key_exists($key, $map)) {
			_define($map[$key], $value);
		}
	}
}

function _loadConcrete() {
	
	$config = BurritoConfig::get('shared', 'concrete');
	$config = array_merge($config, BurritoConfig::env('concrete'));
	
	foreach ($config as $key => $value) {
		_define(strtoupper($key), $value);
	}
}

function _define($key, $value) {
	echo sprintf("define('%s', '%s');", $key, $value), "\n";
}
