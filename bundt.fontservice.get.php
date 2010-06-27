<?php

	header("Content-type: application/xml");
	
	//#todo move to db
	
	$_CLEAN['font'] = filter_input(INPUT_GET, 'font', FILTER_SANITIZE_STRING);
	$_CLEAN['format'] = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_STRING);
	
	$font_map = array(
		"OpenBaskerville" => "fonts/open-baskerville/OpenBaskerville.ufo/",
		"UbuntuTitle" => "fonts/UbuntuTitle/UbuntuTitleBold.svg",
		"Puritan" => "fonts/puritan/Puritan.sfd",
		"DemoFont" => "fonts/robo-fab-demo/DemoFont.ufo/",
		"W3C-fonts-kern-01-t" => "fonts/Tests-W3C/fonts-kern-01-t.svg",
		"bundt-test-font" => "fonts/Tests-Bundt/bundt.tests.testsuite-font.svg"
	);
	
	if(is_file($font_map[$_CLEAN['font']])) {
		echo file_get_contents($font_map[$_CLEAN['font']]);
	} else {
		header("HTTP/1.0 404 Not Found");
	}
?>
