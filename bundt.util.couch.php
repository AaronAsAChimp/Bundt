<?php

class Couch {
	protected $location = "";
	protected $document = null;
	protected $database = null;
	protected $response = "";
	
	// todo: move to an abstract class
	protected function rest_get($path) {
		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'GET',
				'ignore_errors' => true
			)
		));
		$this->response = file_get_contents($this->location . $path,  false, $context);
		return json_decode($this->response);
	}
	
	// todo: move to an abstract class
	protected function rest_put($path, $body = null) {

		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'PUT',
				/*'header' => array (
					'Content-type' => "application/x-www-form-urlencoded"
				),*/
				'content' => ($body == null)? "": json_encode($body),
				'ignore_errors' => true
			)
		));
		
		$this->response = file_get_contents($this->location . $path,  false, $context);
		return json_decode($this->response);
	}
	
		// todo: move to an abstract class
	protected function rest_post($path, $body = null) {

		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'POST',
				/*'header' => array (
					'Content-type' => "application/x-www-form-urlencoded"
				),*/
				'content' => ($body == null)? "": json_encode($body),
				'ignore_errors' => true
			)
		));
		
		$this->response = file_get_contents($this->location . $path,  false, $context);
		return json_decode($this->response);
	}
	
	// todo: move to an abstract class
	protected function rest_delete($path) {
		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'DELETE',
				'ignore_errors' => true
			)
		));
		$this->response = file_get_contents($this->location . $path,  false, $context);
		return json_decode($this->response);
	}
	
	protected function build_path() {
		return $this->database . "/" . (($this->document)? $this->document . "/": "");
	}
	
	function __construct($location) {
		$this->location = $location;
	}
	
	function __invoke($doc, $db = null) {
		// if just the first parameter is specified were just referencing the database
		// if both are specified then were referencing a document in a database
		if($db != null) {
			$this->document = $doc;
			$this->database = $db;
		} else {
			$this->document = null;
			$this->database = $doc;
		}
		return $this;
	}
	
	function relax() {
		return $this->rest_get("");
	}
	
	function create() {
		return $this->rest_put($this->build_path());
	}
	
	function delete() {
		return $this->rest_delete($this->build_path());
	}
	
	function put($body) {
		if($this->document) {
			return $this->rest_put($this->build_path(), $body);
		} else {
			return $this->rest_post($this->build_path(), $body);
		}
	}
	
	function get() {
		return $this->rest_get($this->build_path() . (($this->document)?"": "_all_docs/"));
	}
}

$couch = new Couch("http://localhost:5984/");
