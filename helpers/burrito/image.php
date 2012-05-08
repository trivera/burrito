<?php 

defined('C5_EXECUTE') or die('Access Denied.');

class BurritoImageHelper extends ImageHelper {
	
	public function outputThumbnail($obj, $width=100, $height=100, $params=array()){
		
		$buh = Loader::helper('burrito/utility', 'burrito');
		
		$buh->defaults(array(
			'obj'		=> $obj,
			'width'		=> $width,
			'height'	=> $height,
			'alt'	 	=> null,
			'return' 	=> true,
			'crop'		=> false
		), $params);

		return call_user_func_array(array('parent', 'outputThumbnail'), $params);
	}
	
}