<?php

require_once("bundt.util.session.php");
include("bundt.tests.harness.php");

class SessionTestSuite extends Harness {

	public function setup() {
		$this->set_suite_title("Bundt - Session Test Suite");
		$this->set_suite_description("<p>These tests test Bundt's custom Session API.</p>");
	}

	public function run() {

		$this->set_title("Session Object Allocation Test");
		$sess1 = new Session("test1");
		
		$this->set_description("construct the object.");
		$this->assert($sess1);
		
		$this->set_description("initialize the session.");
		$this->assert( $_SESSION );
		
		////////////////////////////////////////////////////////////////////////

		$this->store("session", $_SESSION);
		
		////////////////////////////////////////////////////////////////////////
		
		$this->set_title("Session Assignment Test");
		$sess1["array set test"] = 1024;
		
		$this->set_description("assign the value to the session.");
		$this->assert($sess1["array set test"]);
		
		$this->set_description("retrieve the value from the session.");
		$this->equals($sess1["array set test"], 1024);
		
		////////////////////////////////////////////////////////////////////////

		$this->set_title("Session Count Test");
		$this->set_description("add 100 elements to the session.");
		for($i = 0; $i < 100; $i++) {
			$sess1["array loop test " . $i] = $i;
		}

		$this->equals( count($sess1), 101 );
		
		////////////////////////////////////////////////////////////////////////

		$this->set_title("Session Iteration Test");
		$this->set_description("iterate over the elements in the session.");
		
		$loop_count = 0;
		foreach($sess1 as $key => $value) {
			$loop_count++;
		}
		$this->equals($loop_count, 101);
		
		////////////////////////////////////////////////////////////////////////
	
		$this->set_title("Session 2 Object Allocation Test");
		$this->set_description("construct the object.");
		
		$sess2 = new Session("test2");
		$this->assert($sess2);
		
		////////////////////////////////////////////////////////////////////////

		$this->set_title("Session 2 Retrival Test");
	
		$sess2["set 1"] = 32;
		$sess2["set 2"] = 64;
		
		$this->set_description("retrieve the first assigned element.");
		$this->equals( $sess2["set 1"], 32);
		
		$this->set_description("retrieve the second assigned element.");
		$this->equals($sess2["set 2"], 64);
		
		////////////////////////////////////////////////////////////////////////
		
		$this->set_title("Session Destruction Test");
		
		Session::destroy();
		
		$this->set_description("destroy all first session");
		$this->assert_not($sess);
		
	}
	
	public function teardown() {
		if(session_id() !== "") {
			$_SESSION = array();
			session_destroy();
		}
	}

}

new SessionTestSuite();
