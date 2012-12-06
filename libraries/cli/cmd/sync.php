<?php

// cannot run in production
if (BURRITO_ENV == 'production') {
	echo $tty->error('this command cannot be run on production server');
	exit;
}

// arguments
if (count($args) > 1) {
	echo $tty->error('burrito-sync expects a maximum of 1 argument');
	exit;
}
elseif (empty($args)) {
	$args[] = 'all';
}


// DATA
if (in_array($args[0], array('data', 'all'))) {
	
	// create backup directory
	if (!is_writable(__C5__.($_=BurritoConfig::env('sync', 'backup', 'dir')))) {
		echo $tty->h1('First time sync');
		echo $tty->func("creating directory for backup files: {$_}"), "\n";
		if (mkdir(__C5__.$_)) {
			echo $tty->success('done');
		}
		else {
			echo $tty->error('could not create directory for backup files');
			exit;
		}
	}

	// export
	echo $tty->h1('Downloading remote database: '. BurritoConfig::get('production', 'db', 'name'));
	
	// system call
	echo $tty->println($_=sprintf(
		'ssh %s "mysqldump %s | gzip" > %s',
		_ssh(BurritoConfig::env('sync', 'ssh')),
		_mysql(BurritoConfig::get('production', 'db')),
		($target=__C5__.$_.'/'.($f=time().'.sql.gz'))
	));
	
	passthru($_, $status);

	// export failed
	if ($status != 0){
		echo $tty->error('database export failed; please verify your remote ssh/mysql credentials');
		exit;
	}
	
	// export success; proceed with import
	else {
		echo $tty->println("saved {$f}");
		echo $tty->success('done');
		
		// import
		echo $tty->h1('Importing local database: '.BurritoConfig::get('development', 'db', 'name'));
		
		// check for mysql
		passthru('hash mysql 2>/dev/null', $status);
		if ($status != 0) {
			echo $tty->error('no access to mysql bin. please add it to your PATH');
			exit;
		}
		
		// system call
		echo $tty->println($_=sprintf(
			'gzip -dc %s | mysql %s',
			$target,
			_mysql(BurritoConfig::get('development', 'db'))
		));
		passthru($_, $status);

		// import failed
		if ($status != 0){
			echo $tty->error('database import failed; please verify your local mysql credentials');
			exit;
		}
		
		// import success
		else {
			echo $tty->success('done');
		}
	}
}



// FILES
if (in_array($args[0], array('files', 'all'))) {
	
	echo $tty->h1('Syncing files');
	echo $tty->println($_ = 'rsync  '._rsync(BurritoConfig::env('sync', 'files')));
	
	// system call
	passthru($_, $status);
	
	// sync failed
	if ($status == 23) {
		echo $tty->error('rsync failed; some files could not be copied');
		exit;
	}
	elseif ($status != 0){
		echo $tty->error('rsync failed; please verify remote ssh credentials');
		exit;
	}
	
	// sync success
	else {
		echo $tty->success('done');
	}
}



// HELPERS
function _ssh($options) {
	
	if (!is_array($options)) {
		echo $tty->error('invalid ssh options');
		exit;
	}
	
	// base
	ob_start();
	
	// port
	if ($_=$options['port']) {
		echo "-p {$_} "; 
	}
	
	// user@host
	echo $options['user'], '@', $options['host'];
	
	// path
	if (array_key_exists('path', $options)) {
		echo ':'.$options['path'];
	}
	
	return ob_get_clean();
}


function _mysql($options) {
	
	if (!is_array($options)) {
		echo $tty->error('invalid mysql options');
		exit;
	}
	
	// base
	ob_start();
	
	// user
	if ($_=$options['user']) {
		echo "-u{$_} ";
	}
	
	// password
	if ($_=$options['pass']) {
		echo "-p{$_} ";
	}
	
	// host
	if ($_=$options['host']) {
		echo "-h{$_} ";
	}
	
	// port
	if ($_=$options['port']) {
		echo "-P{$_} ";
	}
	
	// db name
	echo $options['name'];
	
	return ob_get_clean();
}

function _rsync($options) {
	
	if (!is_array($options)) {
		echo $tty->error('invalid mysql options');
		exit;
	}
	
	// base
	ob_start();
	
	$ssh = BurritoConfig::env('sync', 'ssh');
	$port = ($ssh['port']) ? $ssh['port'] : 22;
	
	echo sprintf(
		'-rvtzp --exclude="backups/*" --exclude="cache/*" --exclude="tmp/*" --exclude="trash/*" --progress --rsh="ssh %s" %s',
		'-p'.$port,
		$ssh['user'].'@'.$ssh['host'].':'.$options['remote_dir'].' '.__C5__.$options['local_dir']
	);
	
	return ob_get_clean();
}







