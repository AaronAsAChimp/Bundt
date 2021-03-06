<?php

abstract class Harness {
	protected $suite_title = "Unnamed Test Suite";
	protected $suite_description = "";
	protected $title = "Unnamed Test";
	protected $success_description = "complete the test.";
	protected $result = false;
	protected $counts = null;
	protected $counts_old = null;
	protected $failed_tests = "";
	protected $storage = null;
	protected $start_time = 0;
	protected $result_tracking = array();
	
	final protected function fail() {
		$this->test(false);
	}
	
	final protected function assert(&$result) {
		$this->test(isset($result));
	}
	
	final protected function assert_not(&$result) {
		$this->test(!isset($result));
	}
	
	final protected function equals($var, $val) {
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

		echo "<div id=\"test", $this->counts["tests"], "\" class=\"", $pf," test\">";
			
			echo "<h3 class\"description\">";
			echo $this->get_description();
			echo "</h3>";
			echo "<div class=\"conclusion\">The test <strong>", $pf, "</strong>.</div>";
			
			echo "<ul class=\"count-deltas\">";
			foreach($this->counts as $name => $count) {
				$delta = $count - $this->counts_old[$name];
				echo "<li> +<span class=\"delta\">", $delta, "</span> ", $name, "</li>";
			}
			echo "</ul>";
			
			$this->counts_old = $this->counts;
			
			$this->print_stored();
		echo "</div>";
	}
	
	final protected function set_suite_title($title) {
		$this->suite_title = $title;
	}
	
	final protected function set_suite_description($desc) {
		$this->suite_description = $desc;
	}
	
	final protected function set_title($title) {
		$this->title = $title;
		echo "</div>","<div class=\"ac\"></div>", "<div class=\"test-group\">";
		echo "<h2>", $this->get_title(), "</h2>";
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
	
	final protected function get_tracking_file_name() {
		$obj = new ReflectionObject($this);
		return "test-results/" . $obj->getShortName() . ".json";
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
	
	final public function print_error($class, $string, $file, $line, $context) {
		echo "<div class=\"", $class ,"\">", $string, "<br />Line: ", $line, "<br />File: ", $file, "<br /><pre>";
		var_dump($context);
		echo "</pre>";
		$this->print_stored();
		echo "</div>";
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
		
		$this->counts_old = $this->counts = array(
			"tests" => 0,
			"pass" => 0,
			"fail" => 0,
			"exceptions" => 0,
			"errors" => 0,
			"warnings" => 0
		);
		
		error_reporting(E_ALL | E_STRICT); 
		set_error_handler(function($num, $string, $file, $line, $context) {
			global $TEST_HARNESS_FRAMEWORK;
			if(is_object($TEST_HARNESS_FRAMEWORK)) {
				
				switch($num) {
					case E_WARNING:
					case E_USER_WARNING:
					case E_NOTICE:
					case E_USER_NOTICE:
					case E_STRICT:
					case E_DEPRECATED:
					case E_USER_DEPRECATED:
					$TEST_HARNESS_FRAMEWORK->print_error("warning", $string, $file, $line, $context);
					$TEST_HARNESS_FRAMEWORK->increment("warnings");
					break;
					
					default:
					$TEST_HARNESS_FRAMEWORK->print_error("error", $string, $file, $line, $context);
					$TEST_HARNESS_FRAMEWORK->increment("errors");
					break;
				}
			} else {
				return false;
			}
		});
		
		$this->storage = array();
		$this->setup();
		
		// Setup test tracking
		$working_dir = getcwd();
		chdir( dirname( __FILE__ ) );
		if(is_file($this->get_tracking_file_name())) {
			$this->results_tracking = json_decode(file_get_contents($this->get_tracking_file_name()), true);
		}
		chdir( $working_dir );
		
		echo "<html>";
		echo "<head><title>", $this->suite_title, "</title>";
		echo "<script src=\"libs/jquery.js\"></script>";
		echo "<script src=\"libs/raphael.js\"></script>";
		echo "<script src=\"libs/graphael/g.raphael.js\"></script>";
		echo "<script src=\"libs/graphael/g.line.js\"></script>";

		echo <<<STYLES
<style>
	#page {
		width: 900px;
		margin: 0 auto 200px auto;
		padding:  1%;
		font-family: Verdana;
		font-size: 11px;
		background: #ddd;
	}
	
	#tracker-graph {
		width: 589px;
		height: 248px;
		border: 1px solid black;
		float: left;
		margin: 4px 5px 5px 4px;
	}
	
	.test, .error, .warning, .test-results {
		background: white;
		width: 263px;
		border: 1px solid black;
		height: 230px;
		overflow: auto;
		float: left;
		padding: 9px;
		margin: 4px 5px 5px 4px;
	}
	
	.ac {
		clear:both;
	}

	.test-results {
		border-left: 9px solid black;
	}
	
	.test.passed {
		border-left: 9px solid green;
	}
	
	.warning {
		border-left: 9px solid yellow;
	}
	
	.test.failed, .error {
		border-left: 9px solid red;
	}
	
	.test h2, .test-results h2, .error h2  {
		margin: 0;
	}
	
	.count-deltas {
		margin: 9px 0;
		padding: 0;
		font-size: 9px;
	}
	
	.delta {
		font-weight: bold;
	}
	
	.count-deltas li {
		list-style-type: none;
		display: inline-block;
		margin-right: .6em;
	}
</style>
STYLES;
		echo "</head>";
		echo "<body><div id=\"page\">";
		
		echo "<div class=\"test-suite\">";
		echo "<h1>", $this->suite_title, "</h1>";
		if($this->suite_description) {
			echo "<div class=\"suite-description\">", $this->suite_description; // this tag is closed in the destructor
		}

		$this->start_time = microtime(true);
		
		try {
			$this->run();
		} catch (Exception $e) {
			$this->increment("exceptions");	
			$this->print_error("error", $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
		}
	}
	
	function __destruct () {
		$end_time =  microtime(true) -$this->start_time;
		$this->teardown();
		
		restore_error_handler();
		error_reporting();

		$working_dir = getcwd();
		chdir( dirname( __FILE__ ) );
		$archive_contents = "";
		
		//if(is_writable($this->get_tracking_file_name())) {
			
			$this->results_tracking[time()] = $this->counts;
			$this->results_tracking[time()]["elapsed"] = $end_time;
			
			$archive_contents = json_encode($this->results_tracking);
		
			$archive_success = file_put_contents($this->get_tracking_file_name(), $archive_contents);
		//} else {
		//	$archive_success = false;
		//}
		chdir($working_dir);

		echo "</div>","<div class=\"ac\"></div>";
		echo "<div class=\"test-results\"><h2>The tests took ", $end_time, "s</h2>";
		if($archive_success) {
			echo "<p>These test results were archived in <code>" . $this->get_tracking_file_name() . "</code>.</p>";
		} else {
			echo "<p>There was a problem attempting to archive the test results.</p>";
		}
		echo"<ul>";
		foreach($this->counts as $name => $count) {
			echo "<li>", $name, ": ", $count, "</li>";
		}
		echo "</ul>";
		
		if( $this->failed_tests ) {
			echo "<h2>Tests That Failed</h2><ul>", $this->failed_tests, "</ul>";
		}
		
		$this->print_stored();
		echo "</div>";
		echo "<div id=\"tracker-graph\"></div>";
		echo "</div>";
		
		echo "<div class=\"ac\"></div></div>";
		echo <<<SCRIPTS
<script>
	$(function () {
		var tracking = $archive_contents;
		var indexes = [];
		var time = [];
		var tests = [[],[]];
		var errors = [[],[],[]];
		var i = 0;
		var accum = 0;
		$.each(tracking,function (index) {
			time[i] = this.elapsed * 10000;
			accum += this.elapsed * 10000;
			
			// the tests that failed, add the tests that passed for a stacked graph
			tests[1][i] = this.pass + this.fail;
			// the tests that passed
			tests[0][i] = this.pass;
			
			errors[2][i] = this.warnings;
			errors[1][i] = this.exceptions;
			errors[0][i] = this.errors;
			
			indexes[i] = i++;
		})
		
		var canvas = Raphael("tracker-graph", $("#tracker-graph").width(), $("#tracker-graph").height());
		var graph_height =  $("#tracker-graph").height() / 3;
		
		canvas.g.linechart(0,0, $("#tracker-graph").width(), graph_height, [indexes, [0,i - 1]], [time, [accum / i, accum / i]]);
		var eg = canvas.g.linechart(0,graph_height, $("#tracker-graph").width(), graph_height, [indexes, indexes, indexes], errors);
		eg.lines[0].attr("stroke", "red");
		eg.lines[1].attr("stroke", "orange");
		eg.lines[2].attr("stroke", "yellow");
		
		var tg = canvas.g.linechart(0,graph_height*2, $("#tracker-graph").width(), graph_height, [indexes, indexes], tests, {shade: true});
		tg.lines[0].attr("stroke", "rgba(0, 128, 0)");
		tg.shades[0].attr("fill", "rgba(0, 128, 0)");
		tg.lines[1].attr("stroke", "rgba(255, 0, 0)");
		tg.shades[1].attr("fill", "rgba(255, 0, 0)");
	})
</script>
SCRIPTS;
		echo "</body>";
		echo "</html>";
	}
}
