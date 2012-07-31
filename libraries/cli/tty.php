<?php

class Tty {
	
	private $colors = array(
		'dBlack'=>30,
		'dRed'=>31,
		'dGreen'=>32,
		'dYellow'=>33,
		'dBlue'=>34,
		'dPurple'=>35,
		'dCyan'=>36,
		'dWhite'=>37,
		'Black'=>90,
		'Red'=>91,
		'Green'=>92,
		'Yellow'=>93,
		'Blue'=>94,
		'Purple'=>95,
		'Cyan'=>96,
		'White'=>97,
	);
	
	private $background_colors = array(
		'dBlack'=>40,
		'dRed'=>41,
		'dGreen'=>42,
		'dYellow'=>43,
		'dBlue'=>44,
		'dPurple'=>45,
		'dCyan'=>46,
		'dWhite'=>47,
		'Black'=>100,
		'Red'=>101,
		'Green'=>102,
		'Yellow'=>103,
		'Blue'=>104,
		'Purple'=>105,
		'Cyan'=>106,
		'White'=>107,
	);
	
	private function code($s) {
		return array_key_exists($s, $this->colors)
			? $this->colors[$s]
			: 90
		;
	}
	
	private function background_code($s) {
		return array_key_exists($s, $this->background_colors)
			? $this->background_colors[$s]
			: 107
		;
	}
	
	private function escape($s) {
		return "\033[{$s}m";
	}
	private function color($s) {
		return $this->escape("0;".$this->code($s)."");
	}
	private function bColor($s) {
		return $this->escape($this->background_code($s));
	}
	private function bold($s) {
		return $this->escape("1;".$this->code($s)."");
	}
	private function underline($s) {
		return $this->escape("4;".$this->code($s)."");
	}
	private function reset() {
		return $this->escape(0);
	}

	private function out() {
		$args = func_get_args();
		$newline = $this->extract_newline($args);
		return implode('', $args).$this->reset().$newline;
	}
	
	public function h1($s) {
		return $this->out(
			$this->bold('Yellow'),
			$this->bColor('Blue'),
			$s,
			PHP_EOL
		);
	}

	public function success($s) {
		return $this->out(
			$this->color('dBlack'), 
			$this->bColor('Green'),
			$s,
			PHP_EOL
		);
	}

	public function error($s) {
		return $this->out($this->bold('Red'), $s, PHP_EOL);
	}
	
	public function func($s) {
		return $this->out($this->bold('dBlack'),$s);
	}
	
	public function cmd($s) {
		return $this->out($this->bold('dGreen'),$s);
	}
	
	public function arg($s) {
		return $this->out($this->color('Purple'), $s);
	}
	
	public function default_arg($s) {
		return $this->out($this->underline('Purple'), $s);
	}
	
	public function ghost($s) {
		return $this->out($this->color('Black'), $s);
	}
	
	public function filename($s) {
		return $this->out($this->bold('Cyan'), $s);
	}
	
	public function syntax($s) {
		return $this->out($this->bold('Black'), $s);
	}
	
	public function printf() {
		$args = func_get_args();
		$newline = $this->extract_newline($args);
		return $this->out(vsprintf(array_shift($args), $args), $newline);
	}
	
	public function println() {
		$args = func_get_args();
		$args[] = PHP_EOL;
		return call_user_func_array(array($this, 'printf'), $args);
	}
	
	private function extract_newline(&$args) {
		return end($args) == PHP_EOL
			? array_pop($args)
			: null
		;
	}
}
