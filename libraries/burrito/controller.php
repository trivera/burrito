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
    
}