<?php 

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoController extends Controller {
    
    public function attach_flash() {
        
        if (FlashHelper::has('error') > 0) {
            $messages = FlashHelper::get('error', true);
            $this->set('error', array($messages[0]));
        }
        
        elseif (FlashHelper::has('notice')) {
            $messages = FlashHelper::get('notice', true);
            $this->set('message', $messages[0]);
        }
    }

	// convenience wrapper for coalesce helper
	protected function coalesce(){
		$args = func_get_args();
		$buh = Loader::helper('burrito/utility', 'burrito');
		return call_user_func_array(array($buh, 'coalesce'), $args);
	}
    
}