<?php

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoMailHelper {
	
	public function send($from, $to, $subject, $template, $vars=array(), $headers=array()) {
		
		// headers
		$buh = Loader::helper('burrito/utility', 'burrito');
		$buh->defaults(array(
			'To' => ($to=$this->format_address($to)),
			'From' => $this->format_address($from),
			'Reply-To' => $this->format_address($from),
			'Content-Type' => 'text/html',
		), $headers);
		
		// template
		$template = DIR_FILES_EMAIL_TEMPLATES."/{$template}.html.php";
		$buh = Loader::helper('burrito/utility', 'burrito');
		$body = $buh->capture_template($template, $vars);
		$headers = $this->format_headers($headers);
		
		// send
		return mail($to, $subject, $body, $headers);
	}
	
	public function format_address($address) {
		
		// address as array
		// array('name'=>'John Smith', 'address'=>'john.smith@example.com');
		if (is_array($address) && array_key_exists('address', $address)) {
			return array_key_exists('name', $address) && !empty($address['name'])
				? sprintf('"%s" <%s>', $address['name'], $address['address'])
				: $address['address']
			;
		}
		
		// address as string
		else {
			return $address;
		}
	}
	
	private function format_headers($headers) {
		
		$_ = array();
		
		foreach($headers as $key => $value) {
			if ( is_numeric($value) || ! empty($value)) {
				$_[] = $key . ": " . $value;
			}
		}
		
		return implode("\r\n", $_);
	}
}
