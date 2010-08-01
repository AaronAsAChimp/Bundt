<?php
	echo <<< PROLOG
<?xml version="1.0" encoding="UTF-8"?>
PROLOG;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:svg="http://www.w3.org/2000/svg" xml:lang="en" lang="en">
<head>
<script src="libs/jquery.js" type="text/javascript"></script>
<script src="js/jquery.bundt.extensions.js" type="text/javascript"></script>
<script>
	$(function () {
	
		$("#email").typing_stopped(function () {
			//console.log(($(this).val().split("@", 2))[1]);
			switch(($(this).val().split("@", 2))[1]) {
				case "gmail.com":
					$("#openid-google-label").addClass("highlight");
					break;
				case "yahoo.com":
					$("#openid-yahoo-label").addClass("highlight");
					break;
				case "hotmail.com":
				case "live.com":
				// are there any others?
					$("#openid-microsoft-label").addClass("highlight");
					break;
				default:
					console.log("Unknown");
					break;
			}
		});
	});
</script>
<style type="text/css">
	.highlight {
		background: red;
	}
	
	.highlight:after {
		content: "«";
	}
</style>
</head>
<body>
	<form action="/authservice/signup/" method="post">
		<label for="name">Your Name</label><input type="text" name="name" id="name" placeholder="John Smith" />
		<label for="email">Email Address</label><input type="email" name="email" id="email" placeholder="johnsmith@gmail.com" />
		<label for="website">Webiste</label><input type="url" name="site" id="site" placeholder="http://www.example.com" />
		
		<div class="large-button">
			<label id="openid-google-label"><input type="radio" name="openid" id="openid-google" value="www.google.com/accounts/o8/id" />Google</label>
		</div>
		
		<div class="large-button">
			<label id="openid-yahoo-label"><input type="radio" name="openid" id="openid-yahoo" value="me.yahoo.com" />Yahoo!</label>
		</div>
		
		<div class="large-button">
			<label id="openid-launchpad-label"><input type="radio" name="openid" id="openid-launchpad" value="launchpad.net/~" />Launchpad</label>
			<input type="text" name="openid-launchpad-username" id="openid-launchpad-username" placeholder="Launchpad Username" />
		</div>
		
		<div class="large-button">
			<label id="openid-other-label"><input type="radio" name="openid" id="openid-other" value="other" />OpenID</label>
			<input type="text" name="openid-other-url" id="openid-other-url" placeholder="OpenID Identifier" />
		</div>
		
		<input type="submit" value="Sign up &raquo;" />
	</form>
</body>
</html>
