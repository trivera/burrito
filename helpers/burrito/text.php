<?php

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoTextHelper extends TextHelper {
	
	// foo_bar_bof => fooBarBof
	public function camelBack($string) {
		$_ = $this->camelcase($string);
		$_{0} = strtolower($_{0});
		return $_;
	}
	
}