<?php

// killswitches 
#if ( !isset($_SERVER['SHELL'])) {
#	die('burrito_cli must be run from the command line');
#}


// constants
define('__C5__', $path);
define('__CLI__', dirname(__FILE__));
$script = array_shift($_SERVER['argv']);
$cmd = array_shift($_SERVER['argv']);
$args = $_SERVER['argv'];


// command inclusion helpers
function cmd_path($cmd) {
	return sprintf('%s/cmd/%s.php', __CLI__, $cmd);
}

function cmd_exists($cmd) {
	return file_exists(cmd_path($cmd));
}

function cmd_exec($cmd) {
	global $script, $args, $tty;
	require_once cmd_path($cmd);
}


// dependencies
require_once __CLI__.'/../burrito/config.php';
BurritoConfig::loadConfig();

require_once __CLI__.'/tty.php';
$tty = new Tty();

// init
cmd_exec( cmd_exists($cmd) ? $cmd : 'help' );
