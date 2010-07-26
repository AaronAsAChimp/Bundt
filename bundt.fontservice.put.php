<?php

	require_once("bundt.fontservice.rest.php");
	
	class Put extends Rest {
		
		function header() {
			parent::header();
		}
		
		function body() {
			echo $this->data;
		}
	}
	
	new Put;
