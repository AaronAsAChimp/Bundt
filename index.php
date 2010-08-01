<?php
require_once "bundt.util.couch.php";

// if there is a settings file skip installation
if(!is_file("bundt.settings.php")) {
	// check if CouchDB is installed and running
	$couch->relax();
	
} else {
	include "bundt.views.editor.xhtml";
}
