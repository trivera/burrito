<?php

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoUtilityHelper {

    /**
     * return the first non-null value
     * @param mixed $input,...
     * @return mixed
     */
    public function coalesce(){
        $args = func_get_args();
        foreach($args as $arg){
            if(!is_null($arg) && !empty($arg)){
                return $arg;
            }
        }
        return null;
    }


    /**
     * Build array on defaults
     * @param array $base
     * @param array $overrides
     * @return void
     */
    public function defaults($base, &$overrides){
        $overrides = array_merge($base, $overrides);
    }
    
    
    /**
     * dasherize a string.  Foo's Bar => foos-bar
     * @param string $string 
     * @return string
     */
    public function dasherize($string){
        return preg_replace(array('/[^a-z\d\s]/', '/\s+/'), array('', '-'), strtolower($string));
    }
    
    
    /**
     * return array with only numeric values; keys are preserved
     * @param Array $array 
     * @param bool $filter - perform an array_filter on the array 
     * @return Array
     */
    public function numeric_values(Array $array, $filter=false){
        $ret = array();
        
        foreach($array as $k => $v){
            if (is_numeric($v)){
                $ret[$k] = $v;
            }
        }
        
        return $filter ? array_filter($ret) : $ret;
    }

	public function capture_template($path, $vars=array()){
		ob_start();
		extract($vars);
		include($path);
		return ob_get_clean();
	}

}
