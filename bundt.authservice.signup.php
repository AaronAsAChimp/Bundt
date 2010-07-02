<?php

	require 'libs/openid.php';
	
	$_CLEAN_GET = filter_input_array( INPUT_GET, array(
		'openid_mode' => FILTER_SANITIZE_STRING,
		'openid_identity' => FILTER_SANITIZE_STRING
	));
	
	$_CLEAN_POST = filter_input_array( INPUT_POST, array(
		'name' => FILTER_SANITIZE_STRING,
		'email' => FILTER_SANITIZE_STRING,
		'site' => FILTER_SANITIZE_URL,
		'openid' => FILTER_SANITIZE_URL
	));

	try {
		if(!isset($_CLEAN_GET['openid_mode'])) {
	        $openid = new LightOpenID;
	        $openid->identity = $_CLEAN_POST['openid'];
	        header('Location: ' . $openid->authUrl());

		} elseif($_CLEAN_GET['openid_mode'] == 'cancel') {
		    echo 'User has canceled authentication!';
		} else {
		    $openid = new LightOpenID;
		    echo 'User ' . ($openid->validate() ? $_CLEAN_GET['openid_identity'] . ' has ' : 'has not ') . 'logged in.';
		}
	} catch(ErrorException $e) {
		echo $e->getMessage();
	}
	
		
	echo "<pre>";
	var_dump($_GET, $_CLEAN_GET);
	var_dump($_POST, $_CLEAN_POST);
	echo "</pre>";
