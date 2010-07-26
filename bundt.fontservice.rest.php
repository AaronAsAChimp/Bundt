<?php

abstract class Rest {
	protected $data = null;
	protected $font_name = "";
	protected $font_format = "";
	
	/*class Response {
		const OK = 200;
		const NOT_FOUND = 404;
	}*/
	
	protected $http_mime_type = "application/xml";
	//$http_response = Rest::Response::NOT_FOUND;
	
	//#todo move to db
	private $font_map = array(
		"OpenBaskerville" => "fonts/open-baskerville/OpenBaskerville.ufo/",
		"UbuntuTitle" => "fonts/UbuntuTitle/UbuntuTitleBold.svg",
		"Puritan" => "fonts/puritan/Puritan.sfd",
		"DemoFont" => "fonts/robo-fab-demo/DemoFont.ufo/",
		"W3C-fonts-kern-01-t" => "fonts/Tests-W3C/fonts-kern-01-t.svg",
		"bundt-test-font" => "fonts/Tests-Bundt/bundt.tests.testsuite-font.svg"
	);
	
	function __construct () {
		$this->data = file_get_contents("php://input");
		$this->font_name = filter_input(INPUT_GET, "font", FILTER_SANITIZE_STRING);
		$this->font_format = filter_input(INPUT_GET, "format", FILTER_SANITIZE_STRING);
	}
	
	function __destruct() {
		$this->header();
		$this->body();
	}
	
	/**
	 * Get the path to the source font
	 * @returns a relative filesystem path
	 */
	function get_font_location() {
		return $this->font_map[$this->font_name];
	}
	
	function header() {
		header("Content-type: " . $this->http_mime_type);
	}
	
	function body() {}
}
