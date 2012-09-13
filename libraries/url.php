<?php

class Url {
	
	private $components = array('query'=>null);
	
	public function __construct($url=null) {
		if ($url) {
			
			$this->components = array_merge(
				$this->components,
				parse_url($url)
			);
			
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
		
		if (array_key_exists($key, $this->components)) {
			return $key == 'query'
				
				// special key; build query string
				? http_build_query($this->components[$key])
				
				// normal key
				: $this->components[$key]
			;
		}
		
		else return null;
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
			return array_key_exists($key, $this->components['query'])
				? $this->components['query'][$key]
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
		parse_str($this->components['query'], $this->components['query']);
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
		return ($_=$this->query) ? "?{$_}" : null;
	}
	
	private function anchor() {
		return $this->anchor ? "#{$this->anchor}" : null;
	}	
}
