<?php

/*
	this is a sweet little hack for injecting
	config/burrito/*.ini settings into site.php
*/


// get defines
$json = exec(dirname(__FILE__) . "/bin/burrito config json");

// define
foreach (json_decode($json) as $key => $value) {
	define($key, $value);
}
