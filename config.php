<?php

/*
	this is a sweet little hack for injecting
	config/burrito/*.ini settings into site.php
*/


// get defines
exec('php /usr/local/bin/burrito inject config', $cmds);

// eval (forgive me, Lord)
foreach ($cmds as $_) {
	eval($_);
}
