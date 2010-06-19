<?php

	header("Content-type: application/xml");
//#todo move to db
	$font_map = array(
		"OpenBaskerville" => "fonts/open-baskerville/OpenBaskerville.ufo/",
		"Puritan" => "fonts/puritan/Puritan.sfd",
		"DemoFont" => "fonts/robo-fab-demo/DemoFont.ufo/"
	);

//echo $_GET["font"];

//echo $_GET["format"];

//echo $_GET["name"];
	$_CLEAN['font'] = filter_input(INPUT_GET, 'font', FILTER_SANITIZE_STRING);
	$_CLEAN['format'] = "";
	$_CLEAN['name'] = filter_input(INPUT_GET, 'name', FILTER_SANITIZE_STRING);
	
	//phpinfo();
	
	$pull = new XMLReader;
	$pull->open($font_map[$_CLEAN['font']] . "glyphs/contents.plist");
	
	$glif_file = null;
	$current_element = null;
	$key_found = false;
	
	while($glif_file == null && $pull->read()) {
		//echo $pull->name . " - " . $pull->value . "<br />";
		
		switch($pull->nodeType) {
			case XMLReader::ELEMENT:
				$current_element = $pull->name;
				break;
			case XMLReader::END_ELEMENT:
				$current_element = null;
				break;
			case XMLReader::TEXT:
			case XMLReader::CDATA:
				if($current_element == 'key' && $pull->value == $_CLEAN['name']) {
					$key_found = true;
				} else if( $key_found && $current_element == 'string' ) {
					$glif_file = $pull->value;
				}
				break; 
		}
	}
	
	//var_dump( $font_map[$_CLEAN['font']] . "glyphs/" . $glif_file );
	
	echo file_get_contents($font_map[$_CLEAN['font']] . "glyphs/" . $glif_file);
?>
