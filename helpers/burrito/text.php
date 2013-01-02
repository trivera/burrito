<?php

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoTextHelper extends TextHelper {
	
	// foo_bar_bof => fooBarBof
	public function camelBack($string) {
		$_ = $this->camelcase($string);
		$_{0} = strtolower($_{0});
		return $_;
	}
	
	public function phoneNumber($number, $format = '($1) $2-$3') {
		// Jacked from http://stackoverflow.com/questions/4708248/formatting-phone-numbers-in-php ;)
		return preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', $format, $number);
	}
	
}