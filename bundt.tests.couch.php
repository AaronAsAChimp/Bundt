<?php

include("bundt.util.couch.php");
include("bundt.tests.harness.php");


class CouchTestSuite extends Harness {
	function setup() {}

	function run() {
		global $couch;
		$res = "";
		$revision = "";
		$this->store("result", $res);
		$this->store("revision", $revision);
		$this->store("couch", $couch);

		////////////////////////////////////////////////////////////////////////
		
		$this->set_title("Is the API sufficiently relaxed?");
		$this->set_description("check for a properly installed and configured CouchDB.");
		
		$res = $couch
			->relax(); // should be {"couchdb":"Welcome","version": == VERSION NUMBER == }
			
		$this->equals($res->couchdb, "Welcome");

		////////////////////////////////////////////////////////////////////////
			
		$this->set_title("Test create on a database");
		$this->set_description("create a database.");
		
		$res = $couch("bundt_tests")
			->create();
			
		$this->equals($res->db_name, "bundt_tests");
		
		////////////////////////////////////////////////////////////////////////

		$this->set_title("Test put");
		$this->set_description("check for correct id.");
		
		$res = $couch("test01", "bundt_tests")
			->put( array(
				"test" => "testvalue"
			)); // should be {"ok":true,"id":"test01","rev": == REVISION NUMBER == }

		$revision = $res->_rev;
		
		$this->equals($res->_id, "test01");
		
		////////////////////////////////////////////////////////////////////////
		
		$this->set_title("Test Put with out doc id");
		
		$res = $couch("bundt_tests")
			->put( array(
				"test" => "made up value"
			));
			
		$this->set_description("create document with out id");
		$this->assert($res->_id);
			
		$anon_put_id = $res->_id;
		
		////////////////////////////////////////////////////////////////////////
	
		$res = $couch("test01", "bundt_tests")
			->get(); // should be {"_id":"test01","_rev": == REVISION NUMBER MATCHING ABOVE ==,"test":"testvalue"}

		$this->set_title("Test Get");
		$this->set_description("check for correct id.");
		$this->equals($res->_id, "test01");
		
		$this->set_description("check for correct revision.");
		$this->equals($res->_rev, $revision);
		
		$this->set_description("check for correct data.");
		$this->equals($res->test, "testvalue");
		
		////////////////////////////////////////////////////////////////////////

		$res = $couch("bundt_tests")
			->get();
			/* should be 
		{"total_rows":1,"offset":0,"rows":[
		{"id":"test01","key":"test01","value":{"rev": == REVISION NUMBER MATCHING ABOVE == }}
		]}
		*/

		$this->set_title("Test get whole database");
		$this->set_description("get expected number of rows.");
		$this->equals($res->total_rows, 2);
		
		$this->set_description("get the expected revision.");
		$this->equals($res->rows[1]->value->rev, $revision);
		
		$this->set_description("get correct object.");
		$this->equals($res->rows[1]->id, "test01");
		
		////////////////////////////////////////////////////////////////////////
	
		$this->set_title("Test delete on a document");
		$this->set_description("delete the test document.");
		$res = $couch("test01", "bundt_tests")
			->delete();
		$this->equals( $res->ok, true );
		
		////////////////////////////////////////////////////////////////////////
	
		$this->set_title("Test delete on a database");
		$this->set_description("delete the test database.");
		$res = $couch("bundt_tests")
			->delete();
		$this->equals( $res->ok, true );
	}
	
	function teardown() {}
}

new CouchTestSuite ();

