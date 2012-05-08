<?php

// arguments
if (count($args) > 1) {
	echo $tty->error('burrito-install expects a maximum of 1 argument');
	exit;
}
elseif (count($args) < 1) {
	$args[] = 'all';
}

// CLI
if (in_array($args[0], array('cli', 'all'))) {
	
	if (!file_exists('/usr/local/bin/burrito')) {
		echo $tty->func('installing burrito cli helper'), "\n";
	}
	else {
		echo $tty->func('updating burrito cli helper'), "\n";
	}
	
	echo "cp packages/burrito/bin/burrito /usr/local/bin/burrito", "\n";
	
	if (copy(__C5__.'/packages/burrito/bin/burrito', '/usr/local/bin/burrito')) {
		echo $tty->success('done');
		
		echo "chmod 755 /usr/local/bin/burrito\n";
		if (chmod('/usr/local/bin/burrito', 0775)) {
			echo $tty->success('done');
		}
		else {
			echo $tty->error('could not change permssions on /usr/local/bin/burrito');
		}
		
	}
	else {
		echo $tty->error('cannot write to /usr/local/bin');
	}
}

// CONFIG

if (in_array($args[0], array('config', 'all'))) {
	
	// already installed
	if (file_exists(__C5__.'/config/burrito')) {
		# disabled; it's now safe to re-install config
		# echo $tty->error('burrito config templates already installed to config/burrito/');
		# exit;
	}
	else {
		echo $tty->func("creating config/burrito/"), "\n";
		echo "mkdir", __C5__, "/config/burrito", "\n";
		
		// invalid permission
		if (!mkdir(__C5__.'/config/burrito')) {
			echo $tty->error('config/ directory is not writeable');
			exit;
		}
	}
	
	if ($h = opendir(__CLI__.'/config_templates')) {
		while (($_=readdir($h)) !== false) {
			if (preg_match('/\.ini$/', $_)) {
				if (!file_exists($f=__C5__."/config/burrito/{$_}")) {
					echo "creating {$f}\n";
					copy(__CLI__."/config_templates/{$_}", $f);
				}
			}
		}
		closedir($h);
	}
	else {
		echo $tty->error('could not open packages/burrito/cli/config_templates');
		exit;
	}
	
	echo $tty->success('done');
	
}

