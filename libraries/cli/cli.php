<?php

// killswitches 
#if ( !isset($_SERVER['SHELL'])) {
#	die('burrito_cli must be run from the command line');
#}

// fake C5 execute for CLI direct script execution
define('C5_EXECUTE', true);

// constants
define('__C5__', $_SERVER['PWD']);
define('__BURRITO__', __C5__.'/packages/burrito');
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
if (cmd_exists($cmd)) {
	cmd_exec($cmd);
}
else {
	echo $tty->error("burrito-{$cmd} is not a valid command");
	echo "for more help, run: burrito help\n\n";
}
