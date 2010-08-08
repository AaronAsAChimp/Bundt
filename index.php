<?php
require_once "bundt.util.couch.php";

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
				"value" => "http://localhost:5984",
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
		"title" => "Create Database Structure"
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
	$bundt_installer->run();
?>
</body>
</html>
<?php	
} else {
	include "bundt.views.editor.xhtml";
}
