<?php
	class Disconnect extends Action
	{
		// ******************************
		function __construct()
		{
			parent::__construct();		
			
			$this->MustHave[1] = "SelfID";	

			LogMe("Action Object Created -> ".get_class($this));
		}		
		
		// ******************************
		public function Process($Info)
		{			
			global $Data;
			// Get the Object
			$Actor = $Data['World']->GetObject($Info['SelfID']);			
			if ($Actor == NULL)
			{
				return;
			}		
			
			LogMe("Got Disconnect request iD = ".$Actor->ID);

			$Data['Server']->DisconnectID($Actor->ID);			
		}		
	}
?>