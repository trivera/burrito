<?php

// arguments
if (empty($args)) {
	echo $tty->error('burrito-rebuild expects a maximum of 1 argument');
	exit;
}

// man
if (in_array($args[0], array('man'))) {
	
	exec("hash ronn 2>/dev/null", $output, $ret);
	
	if ( $ret != 0) {
		echo $tty->error('man page rebuild require ronn gem');
		echo "try: gem install ronn\n\n";
		exit;
	}
	else {
		echo $tty->success("rebuilding man pages");
		# this line stopped working for whatever reason
		# passthru("export RONN_STYLE=" . __BURRITO__ . "/man/css");
		# passing absolute path of burrito css to ronn
		$burrito_css = __BURRITO__ . "/man/css/burrito.css";
		passthru("ronn --style={$burrito_css},toc " . __BURRITO__ . "/man/burrito.1.ron");
	}
}