<?php
require_once("bundt.util.settings.php");
include("bundt.tests.harness.php");

class SettingsTestSuite extends Harness {

	protected $test_settings = null;
	protected $test_sess = null;

	public function setup() {
		$this->set_suite_title("Bundt - Settings Test Suite");
		$this->set_suite_description("<p>These tests test Bundt's settings manager.</p>");
		
		$this->test_settings = new Settings();
		$this->test_sess = new Session("install");
		
		$this->store("settings", $this->test_settings);
		$this->store("session", $this->test_sess);
	}

	public function run() {
		
		$this->set_title("Test constant settings");
		$this->set_description("assert that no setting is available.");

		try {
			$this->assert_not($this->test_settings["TEST_CONSTANT_SETTING"]);
		} catch (Exception $e) {}
		
		$this->set_description("retrieve a setting from a constant.");		
		
		define("TEST_CONSTANT_SETTING", 1024);
		
		$this->equals($this->test_settings["TEST_CONSTANT_SETTING"], 1024);
		
		////////////////////////////////////////////////////////////////////////
		
		$this->set_title("Test session settings");
		$this->set_description("assert that no setting is available.");
		
		try {
			$this->assert_not($this->test_settings["TEST_SESSION_SETTING"]);
		} catch (Exception $e) {}
		
		$this->set_description("retrieve a setting from a session.");	
		
		$this->test_sess["TEST_SESSION_SETTING"] = 2048;
		
		$this->equals($this->test_settings["TEST_SESSION_SETTING"], 2048);
		
		$this->set_description("set a setting to a session.");	
		
		$this->test_settings["TEST_SESSION_SETTING"] = 4098;
		
		$this->equals($this->test_settings["TEST_SESSION_SETTING"], 4098);
		
		////////////////////////////////////////////////////////////////////////
		
		$this->set_title("Test getting session object");
		$this->set_description("assert that object is non-null");

		$retrieved_session_object = $this->test_settings->get_session();
		$this->assert($retrieved_session_object);
		
		$this->set_description("retrieve a setting directly from the session object.");

		$this->assert($retrieved_session_object["TEST_SESSION_SETTING"], 4096);
		
		$this->set_description("modify setting from retrieved session object.");

		$retrieved_session_object["TEST_SESSION_SETTING"] = 1024;
		$this->assert($this->test_settings["TEST_SESSION_SETTING"], 1024);
		
		////////////////////////////////////////////////////////////////////////
		
		$this->set_title("Test constant setting overriding session");
		$this->set_description("retrieve a setting from a constant even though the session setting exists.");
		
		$this->test_sess["TEST_CONSTANT_SETTING"] = 1023;
		
		$this->equals($this->test_settings["TEST_CONSTANT_SETTING"], 1024);
		
	}
	
	public function teardown() {
		unset($this->test_sess["TEST_SESSION_SETTING"]);
		unset($this->test_sess["TEST_CONSTANT_SETTING"]);
	}

}

new SettingsTestSuite();
