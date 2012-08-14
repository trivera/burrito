<?php

class Url {
	
	private $components = array();
	
	public function __construct($url=null) {
		if ($url) {
			$this->components = parse_url($url);
			$this->parseQuery();
		}
	}
	
	// set url components
	// $url->host = 'example.com'
	// $url->path = '/home'
	public function __set($key, $value) {
		return $this->components[$key] = $value;
	}
	
	// get url components
	// $url->host			returns 'example.com'
	// $url->path			returns '/path'
	public function __get($key) {
		return array_key_exists($key, $this->components)
			? $this->components[$key]
			: null
		;
	}
	
	// get/set query params
	// $url->query('foo', 'bar')  	sets ?foo=bar
	// $url->query('foo')		   	returns 'bar'
	public function query() {
		
		// args
		$args = func_get_args();
		$key = array_shift($args);
		
		// set 
		if (count($args)) {
			return $this->components['query'][$key] = array_shift($args);
		}
		
		// get
		else {
			return array_key_exists($key, (array)$this->query)
				? $this->query[$key]
				: null
			;
		}
	}
	
	// outputs the url
	// don't explicitly call this method, simply:
	// echo $url
	public function __toString() {
		return $this->scheme() .
			$this->user() .
			$this->host .
			$this->port() .
			$this->path .
			$this->queryString() .
			$this->anchor()
		;
	}
	
	private function parseQuery() {
		if (isset($this->components['query'])) {
			parse_str($this->components['query'], $this->components['query']);
		}
	}
	
	private function scheme() {
		return $this->scheme ? "{$this->scheme}://" : null;
	}
	
	private function port() {
		return $this->port ? ":{$this->port}" : null;
	}
	
	private function user() {
		if ($this->user) {
			return $this->pass
				? "{$this->user}:{$this->pass}@"
				: "{$this->user}@"
			;
		}
	}
	
	private function queryString() {
		return is_array($this->query) && count($this->query)
			? "?".http_build_query($this->query)
			: null
		;
	}
	
	private function anchor() {
		return $this->anchor ? "#{$this->anchor}" : null;
	}	
}
