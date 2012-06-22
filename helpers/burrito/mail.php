<?php

defined('C5_EXECUTE') or die("Access Denied.");

class BurritoMailHelper {
	
	private $_data = array();
	private $_mail;
	private $_transport;
	private $_vars = array();
	private $_headers = array();
		
	public function __construct() {
		
		// tap concrete5's Zend mailer object
		$_ = MailHelper::getMailerObject();
		
		// mail object
		$this->_mail = $_['mail'];
		
		// transport
		if (isset($_['transport'])) {
			$this->_transport = $_['transport'];
		}
	}
	
	public function __set($key, $value) {
		return $this->_data[$key] = $value;
	}
	
	public function __get($key) {
		return array_key_exists($key, $this->_data)
			? $this->_data[$key]
			: null
		;
	}
	
	public function init($options=array()) {
		
		$bth = Loader::helper('burrito/text', 'burrito');
		
		foreach ($options as $method_key => $value) {
			if (method_exists($this, $_=$bth->camelBack($method_key))) {
				$this->{$_}($value);
			}
		}
	}
	
	public function from($address) {
		$this->from = $address;
	}
	
	public function to($address) {
		$this->appendAddress('to', $address);
	}
	
	public function cc($address) {
		$this->appendAddress('cc', $address);
	}
	
	public function bcc($address) {
		$this->appendAddress('bcc', $address);
	}
	
	public function replyTo($address) {
		$this->_mail->setReplyTo($address);
	}
	
	public function set() {
		
		$args = func_get_args();
		
		// batch set
		if (count($args) == 1 && is_array($args[0])) {
			foreach ($args[0] as $key => $value) {
				$this->_vars[$key] = $value;
			}
		}
		
		// direct set
		elseif (count($args) == 2) {
			$this->_vars[$args[0]] = $args[1];
		}
		
		// error
		else {
			throw new Exception('BurritoMailHelper#set expects a single array argument or a $key, $value pair of arguments');
		}
	}
	
	
	// currently supports mail templates in root directory
	public function load($template) {
		
		extract($this->_vars);
		
		if (file_exists($_=DIR_FILES_EMAIL_TEMPLATES."/{$template}.php")) {
			include($_);
		}
		else {
			throw new Exception("Could not find mail template: {$_}");
		}
		
		$this->template = $template;
		$this->subject = $subject;
		$this->text = $text;
		$this->html = $html;
	}
	
	// add raw header to mail object
	public function addHeader($key, $value) {
		$this->_headers[$key] = $value;
	}
	
	
	public function send() {
		
		if (ENABLE_EMAILS) {
			
			// from
			if (! $this->from) {
				$this->from = $this->defaultNoReply();
			}
			$this->_mail->setFrom($this->from);
			
			// subject
			$this->_mail->setSubject($this->subject);
			
			// recipients
			$this->injectAddresses('addTo', $this->to);
			$this->injectAddresses('addCc', $this->cc);
			$this->injectAddresses('addBcc', $this->bcc);
			
			// raw headers
			foreach ($this->_headers as $key => $value) {
				$this->_mail->addHeader($key, $value);
			}
			
			// plain text
			if ($this->text) {
				$this->_mail->setBodyText($this->text);
			}
			
			// html
			if ($this->html) {
				$this->_mail->setBodyHtml($this->html);
				
				$this->_mail->setBodyText(strip_tags($this->html));
			}
			
			// send
			try {
				$this->_mail->send($this->_transport);
			}
			catch (Exception $e) {
				$this->log(LOG_TYPE_EXCEPTIONS, array(
					t('Mail Exception Occurred. Unable to send mail: ') . $e->getMessage(),
					$e->getTraceAsString()
				));
			}
		}
		
		if (ENABLE_LOG_EMAILS) {
			$message = ENABLE_EMAILS
				? '**' . t('EMAILS ARE ENABLED. THIS EMAIL WAS SENT TO mail()') . '**'
				: '**' . t('EMAILS ARE DISABLED. THIS EMAIL WAS LOGGED BUT NOT SENT') . '**'
			;
			$this->log(LOG_TYPE_EMAILS, $message);
		}
	}
	
	private function defaultNoReply() {
		return 'noreply@' . str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', BASE_URL);
	}
	
	
	private function splitAddresses($string) {
		$ret = preg_split('/\s*,\s*/', $string);
		
		foreach ($ret as &$_) {
			$_ = trim($_);
		}
		
		return array_filter($ret);
	}
	
	public function appendAddress($key, $address) {
		$this->{$key} = array_merge((array)$this->{$key}, $this->splitAddresses($address));
	}
	
	private function injectAddresses($zend_method, $addresses) {
		
		if (empty($addresses)) {
			return null;
		}
		
		foreach ($addresses as $_) {
			call_user_func(array($this->_mail, $zend_method), $_);
		}
	}
	
	private function log($type, $messages) {
		
		$l = new Log($type, true, true);
		
		// base messages
		foreach ((array)$messages as $_) {
			$l->write($_);
		}
		
		if (ENABLE_LOG_EMAILS) {
			$l->write(t('Template:') . ': ' . $this->_template);
			$l->write(t('To') . ': ' . join(',', $this->to));
			$l->write(t('From') . ': ' . $this->from);
			$l->write(t('Subject') . ': ' . $this->subject);
			$l->write(t('Text') . ': ' . $this->text);
			$l->write(t('Html') . ': ' . $this->html);
		}
		
		$l->close();
	}
	
	
	/**** 
	 ** functions below are currently unused
	 ** may be useful when adding functionality to BurritoMailHelper later
	 **
	
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
	}*/
	
}
