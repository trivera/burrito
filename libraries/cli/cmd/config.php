<?php

// arguments
if (count($args) > 1) {
	echo $tty->error('burrito-config expects a maximum of 1 argument');
	exit;
}
elseif (empty($args)) {
	$args[] = 'debug';
}


// debug
if (in_array($args[0], array('debug'))) {
	print_r(_compileConfig());
	exit;
}

// site.php
if (in_array($args[0], array('json'))) {
	echo json_encode(_compileConfig());
	exit;
}

function _compileConfig() {
	return array_merge(_loadDb(), _loadConcrete());
}

function _loadDb() {
	
	$config = BurritoConfig::env('db');
	
	$map = array(
		'host' => 'DB_SERVER',
		'user' => 'DB_USERNAME',
		'pass' => 'DB_PASSWORD',
		'name' => 'DB_DATABASE',
	);
	
	$_ = array();
	
	foreach ($config as $key => $value) {
		if (array_key_exists($key, $map)) {
			$_[$map[$key]] = $value;
		}
	}
	
	return $_;
}

function _loadConcrete() {
	
	$config = BurritoConfig::get('shared', 'concrete');
	$config = array_merge($config, BurritoConfig::env('concrete'));
	
	$_ = array();
	
	foreach ($config as $key => $value) {
		$_[strtoupper($key)] = $value;
	}
	
	return $_;
}

function _define($key, $value) {
	echo sprintf("define('%s', '%s');", $key, $value), "\n";
}
