<?php

defined('C5_EXECUTE') or die(_("Access Denied."));

class BurritoPackage extends Package {
	
	protected $pkgHandle = 'burrito';
	protected $appVersionRequired = '5.5.0';
	protected $pkgVersion = '1.0.0';
	
	public function on_start() {
		Loader::library('on_start', 'burrito');
	}
	
	public function getPackageDescription() {
		return t("A delicious combination of libraries and scripts to aid in the development of Concrete5 websites.");
	}
	
	public function getPackageName() {
		return t("Burrito");
	}
	
}