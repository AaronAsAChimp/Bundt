<?php

	require 'libs/openid.php';
	require 'bundt.util.session.php';
	require 'bundt.util.couch.php';
	
	$_CLEAN_GET = filter_input_array( INPUT_GET, array(
		'openid_mode' => FILTER_SANITIZE_STRING,
		'openid_identity' => FILTER_SANITIZE_STRING
	));
	
	$_CLEAN_POST = filter_input_array( INPUT_POST, array(
		'name' => FILTER_SANITIZE_STRING,
		'email' => FILTER_SANITIZE_STRING,
		'site' => FILTER_SANITIZE_URL,
		'openid' => FILTER_SANITIZE_URL,
		'openid-launchpad-username' => FILTER_SANITIZE_STRING,
		'openid-other-url' => FILTER_SANITIZE_STRING
	));
	
	$signup_session = new Session("auth");
	
	$openid_url = $_CLEAN_POST['openid'];
	$new_location = "/";
	
	switch($openid_url) {
		case "launchpad.net/~":
			$openid_url = "https://" . $openid_url . $_CLEAN_POST['openid-launchpad-username'];
			break;
		case "other":
			$openid_url =  $_CLEAN_POST['openid-other-url'];
			break;
		default:
			$openid_url = "https://" . $openid_url;
	}

	try {
		if(!isset($_CLEAN_GET['openid_mode'])) {
			//store the sign up data until we get a successful auth response
			$signup_session["post"] = $_CLEAN_POST;
			$signup_session["openid-url"] = $openid_url; 
			
	        $openid = new LightOpenID;
	        $openid->identity = $openid_url;
	        
	        $new_location = $openid->authUrl();

		} elseif($_CLEAN_GET['openid_mode'] == 'cancel') {
			Session::destroy();
		    //echo 'User has canceled authentication!';
		} else {
		    $openid = new LightOpenID;
		    //echo 'User ' . ($openid->validate() ? $_CLEAN_GET['openid_identity'] . ' has ' : 'has not ') . 'logged in.';
		    if($openid->validate()) {
		    	// Is it possible to clobber the record if the same email is used sign up a second time
		    	$couch(strtolower($signup_session["post"]["email"]),"bundt-users")
		    		->put(array(
		    			"email" => $signup_session["post"]["email"],
		    			"name" => $signup_session["post"]["name"],
		    			"openid" => array(
		    				array(
		    					"provider" => $signup_session["openid-url"],
		    					"identity" => $_CLEAN_GET['openid_identity']
		    				)
		    			),
		    			"website" => $signup_session["post"]["site"],
		    			"role" => 1,
		    		));
		    } else {
		    	Session::destroy();
		    }
		}
	} catch(ErrorException $e) {
		echo $e->getMessage();
	}
	
	header('Location: ' . $new_location);
		
	echo "<pre>";
	var_dump($_GET, $_CLEAN_GET);
	var_dump($_POST, $_CLEAN_POST);
	var_dump($openid_url);
	echo "</pre>";
