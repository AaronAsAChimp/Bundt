<?php

if(is_file("bundt.settings.php")) {
	include_once "bundt.settings.php";
} else {
	require_once "bundt.util.session.php";
}

class Settings implements ArrayAccess {
	protected $session = array();
	
	function __construct() {
		$this->session = new Session("install");
	}
	
	function &get_session () {
		return $this->session;
	}
	
	function offsetExists ( $offset ) {
	
		if(defined($offset)) {
			return true;
		} else {
			return isset($this->session[$offset]);
		}

	}
	
	function offsetGet ( $offset ) {
		if(defined($offset)) {
			return constant($offset);
		} else {
			$this->session = new Session("install");
			if (isset($this->session[$offset])) {	
				return $this->session[$offset];
			} else {
				throw new Exception("Bundt Settings Manager - Setting Not Found");
			}
		}
	}
	
	function offsetSet ( $offset , $value ) {
		if(!defined($offset)) {
			$this->session = new Session("install");
			$this->session[$offset] = $value;
		} else {
			throw new Exception("Bundt Settings Manager - Can't Re-define a Constant Setting.");
		}
	}
	
	function offsetUnset ( $offset ) {
	}
}

$settings = new Settings();
