<?php

class Session implements ArrayAccess, Iterator, Countable {
	protected $namespace = "";
	protected $session = array();
	
	function __construct($name) {
		if(session_id() === "") {
			session_start();
		}
		$this->namespace = $name;
		$this->session =& $_SESSION[$name];
	}
	
	public static function destroy() {
		if(session_id() === "") {
			session_start();
		}
		$_SESSION = array();
		session_destroy();
	}
	
	public function offsetSet($offset, $value) {
		$this->session[$offset] = $value;
	}
	public function offsetExists($offset) {
		return isset($this->session[$offset]);
	}
	public function offsetUnset($offset) {
		unset($this->session[$offset]);
	}
	public function offsetGet($offset) {
		return isset($this->session[$offset]) ? $this->session[$offset] : null;
	}
	
	public function rewind() {
		reset($this->session);
	}

	public function current() {
		return current($this->session);
	}

	public function key() {
		return key($this->session);
	}

	public function next() {
		return next($this->session);
	}

	public function valid() {
		return $this->current() !== false;
	}    

	public function count() {
		return count($this->session);
	}
    
}
