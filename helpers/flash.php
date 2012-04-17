<?php 

defined('C5_EXECUTE') or die('Access Denied.');


/**
 * Click F for Flash!
 *
 * FlashHelper::error('push an error to the stack');
 * FlashHelper::error(array('push multiple', 'errors to the stack));
 * FlashHelper::notice('push a message to the stack');
 * FlashHelper::has('error'); // returns true if there is one or more errors
 * FlashHelper::get('error'); // gets errors
 * FlashHelper::get('error', true); // gets errors and empties session
 */
class FlashHelper {

	const KEY = 'flash';

	static public function initialize() {
		if (!is_array($_SESSION[self::KEY])) {
			$_SESSION[self::KEY] = array();
		}
	}

	// setter
	static public function __callStatic($type, $args) {
		if (!array_key_exists($type, $_SESSION[self::KEY])) {
			$_SESSION[self::KEY][$type] = array();
		}

		return $_SESSION[self::KEY][$type] = array_merge($_SESSION[self::KEY][$type], (array)$args[0]);
	}

	// getter
	static public function get($type, $reset) {
		$messages = array_key_exists($type, $_SESSION[self::KEY])
			? $_SESSION[self::KEY][$type]
			: array()
		;

		if ($reset) {
			unset($_SESSION[self::KEY][$type]);
		}

		return $messages;
	}

	// check!
	static public function has($type) {
		return array_key_exists($type, $_SESSION[self::KEY])
			? count($_SESSION[self::KEY][$type])
			: false
		;
	}

	static public function render(){
		if (self::has('error')) {
			Loader::packageElement('errors', 'burrito', array('errors' => self::get('error', true)));
		}
	}

}

// initialize
FlashHelper::initialize();
