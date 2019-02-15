<?php
	class Enter extends Action
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
						
			$this->MustHave[0] = "SelfID";	
			
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
			
			if ($Actor->MyBlock->IsConnector() == false)
			{
				return;
			}

			$Action = array();
			$Action['Action'] = "Teleport";
			$Action['SelfID'] = $Actor->ID;			
			$Action['LocX'] = $Actor->MyBlock->DestX;
			$Action['LocY'] = $Actor->MyBlock->DestY;
			$Data['AIF']->Act($Action);			
		}		
	}
?>