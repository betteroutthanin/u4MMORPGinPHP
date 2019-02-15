<?php
	class ActionInterface extends Base
	{
		private $ActionList;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			LogMe("Action Interface booting . . . ");
			// Create one object for each action type
			$ActionNameList = ScanAndStrip("Classes/Actions");
			
			foreach ($ActionNameList as $ClassName)
			{
				$this->ActionList[$ClassName] = new $ClassName();
			}
			LogMe(" . . . Action Interface booted");
		}
		
		// ******************************
		public function Act($InfoArray)
		{
			// And action Name is the first thing that
			// is needed
			if (array_key_exists("Action", $InfoArray) === false)
			{
				LogMe("Act function was not provied with an Action");
				return;
			}
			
			// This is used in serveral location.
			// Quicker to extract it
			$NameOfAction = $InfoArray['Action'];			
			
			// Make sure the action being requested exist
			// in the action list.
			if (array_key_exists($NameOfAction, $this->ActionList) === false)
			{
				LogMe("Action is not in list -> ". $NameOfAction);
				return;
			}

			// Do the action, PrepAndCheck will call the
			// the actions process function.
			// LogMe("Processing action -> ".$NameOfAction);
			$this->ActionList[$NameOfAction]->PrepAndCheck($InfoArray);									
		}
	}
?>