<?php

class Couch {
	protected $location = "";
	protected $revision = null;
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
		return json_decode($this->response, true);
	}
	
	// todo: move to an abstract class
	protected function rest_put($path, $body = null) {

		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'PUT',
				'header' => "Content-type: application/x-www-form-urlencoded",
				'content' => ($body == null)? "": json_encode($body),
				'ignore_errors' => true
			)
		));
		
		$this->response = file_get_contents($this->location . $path,  false, $context);
		return json_decode($this->response, true);
	}
	
		// todo: move to an abstract class
	protected function rest_post($path, $body = null) {

		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'POST',
				'header' => "Content-type: application/x-www-form-urlencoded",
				'content' => ($body == null)? "": json_encode($body),
				'ignore_errors' => true
			)
		));
		
		$this->response = file_get_contents($this->location . $path,  false, $context);
		return json_decode($this->response, true);
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
		return json_decode($this->response, true);
	}
	
	protected function build_path() {
		return $this->database . "/" . (($this->document)? $this->document . "/": "");
	}
	
	function __construct($location) {
		$this->location = $location;
	}
	
	function __invoke($rev, $doc = null, $db = null) {
		// if just the first parameter is specified were just referencing the database
		// if first two are specified then were referencing a document in a database
		// if all three are specified then were referencing a particular revision of a document in a database
		
		if($rev !== null && $db !== null && $doc !== null) {
			$this->revision = $rev;
			$this->document = $doc;
			$this->database = $db;
		} else if($rev !== null && $doc !== null) {
			$this->revision = null;
			$this->document = $rev;
			$this->database = $doc;
		} else {
			$this->revision = null;
			$this->document = null;
			$this->database = $rev;
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
		return $this->rest_delete($this->build_path() . (($this->revision)?"?rev=" . $this->revision:""));
	}
	
	function put($body) {
		// if there was a revision specified add it to the request body
		if($this->revision) {
			$body["_rev"] = $this->revision;
		}
		
		// if there is a document id use the PUT verb, otherwise use POST
		if($this->document) {
			return $this->rest_put($this->build_path(), $body);
		} else {
			return $this->rest_post($this->build_path(), $body);
		}
	}
	
	function get() {
		$path = $this->build_path();
		if ($this->revision) {
			$path .= "?rev=" . $this->revision;
		} else if(!$this->document) {
			$path .= "_all_docs/";
		} 
		return $this->rest_get($path);
	}
}

$couch = new Couch("http://localhost:5984/");
