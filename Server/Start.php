<?php
	// Utils is needed for LogMe Function
	include_once("Utils/Utils.php");
	LogMe("Log Started", 3, true);
	
	// This application will run for ever.
	set_time_limit(0);
	//ob_implicit_flush();

	// Global Black Board
	$Data = array();
	
	// Include the config file.
	// This has to be done after the $Data
	// object has been created.  this is due
	// to the fact that the $Data object
	// is used to store the config.
	include_once("Config.php");
	
	// Load all the class files
	include_once("Classes/Base.php");
	ScanAndInclude("Classes");
	
	// Create the server object	
	$Server = new Server();
	$Server->BootServer();
	
	// Server created and all data loaded.
	// Main Loop time.
	$Server->TickLoop();
?>