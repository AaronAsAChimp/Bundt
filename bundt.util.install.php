<?php

require_once("bundt.util.settings.php");

class Install {
	protected $session = null;
	protected $settings = null;
	protected $steps = null;
	
	public function step($step) {
		array_push($this->steps, $step);
	}
	
	public function process_settings() {
		foreach($this->steps as $step) {
			if(isset($step["prompt"])) {
				foreach($step["prompt"] as $input) {
					// check for posted data and store it in a setting
					if(isset($input["name"]) && isset($_POST[$input["name"]])) {
						$this->settings[$input["name"]] = filter_input(INPUT_POST, $input["name"], $input["filter"]);
					} else if(!isset($this->settings[$input["name"]]) && isset($input["value"])) {
						$this->settings[$input["name"]] = $input["value"];
					}
				
					// redefine the constants
					//define($input["name"], $this->session[$input["name"]]);
				}
			}
		}
	}
	
	public function run() {
		$require_user_action = false; // this flag stops the processing of further steps to prompt for user input
		
		echo "<ol>";
		foreach($this->steps as $step) {
			$completed = true; // this flag states whether the current step completed
			
			// check for the required steps, the error reporting mechaniam
			// doesn't have to be pretty only programmers will see this,
			// TODO: this could use some refactoring love
			if(!isset($step["title"])) {
				$completed = false;
				$require_user_action = true;
				throw new InstallStepException("title");
			}
			
			if(!isset($step["test"])) {
				$completed = false;
				$require_user_action = true;
				throw new InstallStepException("test");
			}

			if(!isset($step["failure"])) {
				$completed = false;
				$require_user_action = true;
				throw new InstallStepException("failure");
			}
			
			// if the last step completed then do the next one
			if($completed && !$require_user_action) {
				echo "<!--";
				try {				
					$completed = $step["test"]();
					
					// process the automatic install step
					if(!$completed && isset($step["automatic"])) {
						$step["automatic"]();
						$completed = $step["test"]();
					}
					
					// if were here that means $completed *was* true
					// if its no longer true the stop and prompt for user action
					if(!$completed) {
						$require_user_action = true;
					}
				} catch( Exception $e ) {
					echo "Exception thrown:<br/>";
					var_dump($e);	
				}
				echo "-->";
			}
			echo "<li class=\"" , ($completed? "completed": "not-completed"), "\">", $step["title"];
			if(!$completed && $require_user_action) {
				echo "<div class=\"fail-whale\">";
				echo $step["failure"];
				if(isset($step["prompt"])) {
					echo "<form method=\"POST\">";
					foreach($step["prompt"] as $input) {
						echo "<label>", $input["text"];
						echo "<input type=\"", $input["type"], "\" name=\"", $input["name"], "\" value=\"", $input["value"], "\" />";
						echo "</label>";
					}
					echo "<input type=\"submit\" value=\"&raquo;\" />";
					echo "</form>";
				}
				echo "</div>";
			} 
			echo "</li>";
		
		}
		echo "</ol>";
		
		// we've finished all the steps so lets write the settings file
		if(!$require_user_action) {
			$file = "bundt.settings.php";
			if(is_writable($file)) {
				$this->write_settings($file);
			} else {
				echo "<h2>You're almost there</h2>";
				echo "<p>The final step in setting up Bundt was not automatic because we don't have permission to write to this folder. Thats a good thing! You will need to complete this final step yourself.</p>";
				echo "<h3>Your mission should you choose to accept it</h3>";
				echo "<p>Copy the following code, and save it to a file named <code>$file</code> in the Bundt installation folder.</p>";
				echo "<textarea>", $this->build_settings(), "</textarea>";
			}
		}
	}
	
	public function write_settings($file) {	
		file_put_contents($file, $this->build_settings());
	}
	
	public function build_settings() {
		// finish 
		$config = <<<MESSAGE
	/***************************************************************************

	This file was autogenerated by the Bundt installation script

	***************************************************************************/
MESSAGE;
		foreach($this->session as $name => $value) {
			$config .= "\n\n\tdefine(\"" . $name . "\", \"" . $value . "\");\n\n\t////////////////////////////////////////////////////////////////////////////";
		}
		return $config;
	}
	
	public function __construct() {
		global $settings;
		$this->steps = array();
		$this->settings =& $settings;
		$this->session = $this->settings->get_session();
	}
}

class InstallStepException extends Exception {
	function __construct($missing_arg) {
		parent::__construct("The argument \"$missing_arg\" is required");
	}
}
