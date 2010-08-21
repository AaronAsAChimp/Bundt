<?php
// if there is a settings file skip installation
if(!is_file("bundt.settings.php")) {
	require_once "bundt.util.install.php";
	
	$bundt_installer = new Install();
	
	// check if CouchDB is installed and running
	$bundt_installer->step(array(
		"title" => "Install CouchDB",
		"failure" => "You don't have the correct version of CouchDB installed. Please upgrade to the latest version. It can be downloaded from <a href=\"http://couchdb.apache.org/\">http://couchdb.apache.org/</a>",
		"prompt" => array(
			array(
				"type" => "url",
				"text" => "Or maybe we can't find it. Where's it located? We'll check again.",
				"value" => "http://localhost:5984/",
				"name" => "COUCH_LOCATION",
				"filter" => FILTER_SANITIZE_URL
			)
		),
		"test" => function() {
			global $couch;
			$info = $couch->relax();
			return $info && (version_compare($info["version"], "0.10.0", ">="));
		}
	));
	
	// create the database structure
	$bundt_installer->step(array(
		"title" => "Create Database Structure",
		"failure" => "The database could not be initialized. Please check that CouchDB has been setup properly.",
		"test" => function () {
			global $couch;
			$users = $couch("bundt-users")->get();
			$fonts = $couch("bundt-fonts")->get();
			return !isset($users["error"]) && !isset($fonts["error"]);
		},
		"automatic" => function () {
			global $couch;
			$couch("bundt-users")->create();
			$couch("bundt-users")->import("database/bundt-users.json");
			
			$couch("bundt-fonts")->create();
			$couch("bundt-fonts")->import("database/bundt-fonts.json");
		}
	));
	
	$bundt_installer->step(array(
		"title" => "Create Your Account",
		"failure" => " <a href=\"/signup/\">Please create your account &raquo;</a>",
		"test" => function() {
			global $couch;
			return $couch("bundt-users")->count() > 0; // TODO this doesn't count properly
		}
	));
	
	$bundt_installer->step(array(
		"title" => "Assign Administrator Privileges",
		"failure" => "Failed to assign administrator privileges to a user. You may need to do this manually.
			<ol>
				<li>Go to your CouchDB Futon installation</li>
				<li>In the <code>bundt-users</code> database, find the user whom you wish grant administrative privileges</li>
				<li>Change the <code>role</code> property to <code>8</code></li>
			</ol>",
		"test" =>  function () {
			return false;
		},
		"automatic" => function () {
			return false;
		}
	));
	
	/*$bundt_installer->step(array(
		"title" => "Choose some fonts"
	));*/

?>
<html>
<head>
	<title>Bundt Installation Awesomeness</title>
</head>
<body><?php
	
	// process any new config
	$bundt_installer->process_settings();
	
	require_once "bundt.util.couch.php";	
		
	$bundt_installer->run();
?>
</body>
</html>
<?php	
} else {
	include "bundt.views.editor.xhtml";
}
