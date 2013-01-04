<?php 

defined('C5_EXECUTE') or die('Access Denied.');
Loader::helper('form');
class BurritoFormHelper extends FormHelper {
	
	private $textareaIndex = 1;

	public function reset() {
		$this->textareaIndex = 1;
		parent::reset();
	}
	
	public function textarea($key) {
	 	$a = func_get_args();
		
		if ((strpos($key, '[]') + 2) == strlen($key)) {
			$_key = substr($key, 0, strpos($key, '[]'));
			$id = $_key . $this->textareaIndex;
		} else {
			$_key = $key;
			$id = $key;
		}
		
		$str = '<textarea id="' . $id . '" name="' . $key . '" ';
		$rv = $this->getRequestValue($key);
	
		if (count($a) == 3) {
			$innerValue = ($rv !== false) ? $rv : $a[1];
			$miscFields = $a[2];
		} else {
			if (is_array($a[1])) {
				$innerValue = ($rv !== false) ? $rv : '';
				$miscFields = $a[1];
			} else {
				// we ignore this second value if a post is set with this guy in it
				$innerValue = ($rv !== false) ? $rv : $a[1];
			}
		}
	
		$str .= $this->parseMiscFields('ccm-input-textarea', $miscFields);
		$str .= '>' . $innerValue . '</textarea>';
		
		$this->textareaIndex++;
		
		return $str;
	 }

	
}