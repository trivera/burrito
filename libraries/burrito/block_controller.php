<?php 

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoBlockController extends BlockController {

	// convenience wrapper for coalesce helper
	protected function coalesce(){
		$args = func_get_args();
		$buh = Loader::helper('burrito/utility', 'burrito');
		return call_user_func_array(array($buh, 'coalesce'), $args);
	}
    
}