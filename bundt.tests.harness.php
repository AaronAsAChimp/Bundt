<?php

abstract class Harness {
	protected $title = "Unnamed Test";
	protected $success_description = "complete the test.";
	protected $result = false;
	protected $counts = null;
	protected $failed_tests = "";
	protected $storage = null;
	protected $start_time = 0;
	
	final protected function assert(&$result) {
		$this->test(isset($result));
	}
	
	final protected function assert_not(&$result) {
		$this->test(!isset($result));
	}
	
	final protected function equals($var, $val){
		$this->test($var === $val);
	}
	
	final protected function test($result) {
		$this->result = $result;
		$pf = ($this->result)? "passed" : "failed";
	
		// count the number of tests
		$this->increment("tests");
		$this->increment(($result)?"pass":"fail");
	
		if(!$this->result) {
			$this->failed_tests .= "<li><a href=\"#test" . $this->counts["tests"] . "\">" . $this->title . "</a></li>";
		}

		echo "<div id=\"test", $this->counts["tests"], "\" class=\"", $pf,"\" style=\"border: 1px solid black; margin-bottom: 10px;\">";
			echo "<h2>", $this->get_title(), "</h2><div>";
			echo $this->get_description();
			echo "</div>The test <strong>", $pf, "</strong>.";
			$this->print_stored();
		echo "</div>";
	}
	
	final protected function set_title($title) {
		$this->title = $title;
	}
	final protected function get_title() {
		return $this->title;
	}
	
	final protected function set_description($desc) {
		$this->description = $desc;
	}
	final protected function get_description() {
		return (($this->result)? "Succeeded to ": "Failed to ") . $this->description;
	}
	
	final protected function store($name, &$item) {
		$this->storage[$name] =& $item;
	}
	final protected function print_stored() {
		if(count($this->storage) > 0 ) {
			echo "<h2>Stored Variables</h2><dl>";
			foreach($this->storage as $name => $variable) {
				echo "<dt>", $name, "</dt><dd><pre>";
				var_dump($variable);
				echo "</pre></dd>";
			}
			echo "</dl>";
		}
	}
	final protected function &retrieve($name) {
		return $this->storage[$name];
	}
	
	final public function print_error($string, $file, $line, $context) {
		echo "<div class=\"error\">", $string, "<br />Line: ", $line, "<br />File: ", $file, "<br />", $context,  "</div>";
	}
	
	abstract public function setup();
	
	abstract public function run();
	
	abstract public function teardown();
	
	public function increment($count) {
		return ++$this->counts[$count];
	}
	
	function __construct () {
		global $TEST_HARNESS_FRAMEWORK;
		$TEST_HARNESS_FRAMEWORK = $this;
		
		$this->counts = array(
			"tests" => 0,
			"pass" => 0,
			"fail" => 0,
			"exceptions" => 0,
			"errors" => 0
		);
		
		set_error_handler(function($num, $string, $file, $line, $context) {
			global $TEST_HARNESS_FRAMEWORK;
			if(is_object($TEST_HARNESS_FRAMEWORK)) {
				$TEST_HARNESS_FRAMEWORK->print_error($string, $file, $line, $context);
				$TEST_HARNESS_FRAMEWORK->increment("errors");
			} else {
				return false;
			}
		});
		
		$this->storage = array();
		$this->setup();

		echo "<div class=\"test-suite\">";
		$this->start_time = microtime(true);
		
		try {
			$this->run();
		} catch (Exception $e) {
			$this->increment("exceptions");	
			$this->print_error($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
		}
	}
	
	function __destruct () {
		$end_time =  microtime(true) -$this->start_time;
		$this->teardown();
		
		restore_error_handler();
		
		echo "</div>";
		echo "<div class=\"test-results\"><h2>The tests took ", $end_time, "s</h2><ul>";
		foreach($this->counts as $name => $count) {
			echo "<li>", $name, ": ", $count, "</li>";
		}
		echo "</ul>";
		
		if( $this->failed_tests ) {
			echo "<h2>Tests That Failed</h2><ul>", $this->failed_tests, "</ul>";
		}
		
		$this->print_stored();
		echo "</div>";
	}
}
