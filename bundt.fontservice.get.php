<?php

	header("Content-type: application/xml");
	
	//#todo move to db
	
	$font_map = array(
		"OpenBaskerville" => "fonts/open-baskerville/OpenBaskerville.ufo/",
		"UbuntuTitle" => "fonts/UbuntuTitle/UbuntuTitleBold.svg",
		"Puritan" => "fonts/puritan/Puritan.sfd",
		"DemoFont" => "fonts/robo-fab-demo/DemoFont.ufo/"
	);
	
	$_CLEAN['font'] = filter_input(INPUT_GET, 'font', FILTER_SANITIZE_STRING);
	$_CLEAN['format'] = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_STRING);
	$_CLEAN['name'] = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
	
	echo file_get_contents($font_map[$_CLEAN['font']]);
?>
