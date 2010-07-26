<?php
	require_once("bundt.fontservice.rest.php");
	
	class Get extends Rest {
		protected $body = "";
		
		function header() {
			$this->http_mime_type = "image/svg+xml";
			
			parent::header();
			
			if(is_file($this->get_font_location())) {
				$this->body = file_get_contents($this->get_font_location());
			} else {
				header("HTTP/1.0 404 Not Found");
			}
		}
		
		function body() {
			echo $this->body;
		}
	}
	
	new Get;
