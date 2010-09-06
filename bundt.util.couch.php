<?php

require_once("bundt.util.settings.php");

class Couch {
	protected $location = "";
	protected $revision = null;
	protected $document = null;
	protected $database = null;
	protected $response = "";
	protected $view = "";
	
	// todo: move to an abstract class
	protected function rest_get($path) {
		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'GET',
				'ignore_errors' => true
			)
		));
		$this->response = file_get_contents($this->location . $path,  false, $context);
	}
	
	// todo: move to an abstract class
	protected function rest_put($path, $body = null) {
		
		if(is_object($body) || is_array($body)) {
			$body = json_encode($body);
		} else if (is_null($body)) {
			$body = "";
		}

		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'PUT',
				'header' => "Content-type: application/x-www-form-urlencoded",
				'content' => $body,
				'ignore_errors' => true
			)
		));
		
		$this->response = file_get_contents($this->location . $path,  false, $context);
	}
	
	// todo: move to an abstract class
	protected function rest_post($path, $body = null) {
		
		if(is_object($body) || is_array($body)) {
			$body = json_encode($body);
		} else if (is_null($body)) {
			$body = "";
		}
		
		$context = stream_context_create(array(
			'http'=> array(
				'method' => 'POST',
				'header' => "Content-type: application/x-www-form-urlencoded",
				'content' => $body,
				'ignore_errors' => true
			)
		));
		
		$this->response = file_get_contents($this->location . $path,  false, $context);
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
		// if the doc starts with _design treat the rev as the view name
		
		if($rev !== null && $db !== null && $doc !== null) {
			if(strpos($doc, "_design") === 0) {
				$this->view = $rev;
			} else {
				$this->revision = $rev;
			}
			$this->document = $doc;
			$this->database = $db;
		} else if($rev !== null && $doc !== null) {
			$this->revision = null;
			$this->document = $rev;
			$this->database = $doc;
			$this->view = "";
		} else {
			$this->revision = null;
			$this->document = null;
			$this->database = $rev;
			$this->view = "";
		}
		
		$this->response = "";
		return $this;
	}
	
	function &relax() {
		$this->rest_get("");
		return $this;
	}
	
	function &create() {
		$this->rest_put($this->build_path());
		return $this;
	}
	
	function &delete() {
		$this->rest_delete($this->build_path() . (($this->revision)?"?rev=" . $this->revision:""));
		return $this;
	}
	
	function &import ($file) {
		if($this->database && !$this->document && !$this->revision) {
			$contents = file_get_contents($file);
			$this->rest_post($this->build_path() . "_bulk_docs", $contents);	
		}
		
		return $this;
	}
	
	function &put($body) {
		// if there was a revision specified add it to the request body
		if($this->revision) {
			$body["_rev"] = $this->revision;
		}
		
		// if there is a document id use the PUT verb, otherwise use POST
		if($this->document) {
			$this->rest_put($this->build_path(), $body);
		} else {
			$this->rest_post($this->build_path(), $body);
		}
		
		return $this;
	}
	
	function count() {
		$res = null;
		if($this->response) {
			$res = $this->response();
		} else {
			$res = $this->get(array("limit" => 0))->response();
		}
		
		return $res["total_rows"];
	}
	
	function &get($params = array()) {
		$path = $this->build_path();

		if ($this->revision) {
			$params["rev"] = $this->revision;
		} else if($this->view) {
			$path .= "_view/" . $this->view;
		} else if(!$this->document) {
			$path .= "_all_docs/";
		} 
		
		$this->rest_get($path . "?" . http_build_query($params));
		
		return $this;
	}
	
	function response() {
		$out = null;
		if($this->response) {
			$out = json_decode($this->response, true);
		}
		return $out;
	}
}

$couch = new Couch($settings["COUCH_LOCATION"]);
