<?php
	class GetDir extends Action
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->MustHave[0] = "Dir";
			$this->MustHave[1] = "SelfID";	

			LogMe("Action Object Created -> ".get_class($this));
		}
		
		// ******************************
		public function Process($Info)
		{			
			global $Data;
			// Get the Object actor
			$Actor = $Data['World']->GetObject($Info['SelfID']);
			
			if ($Actor == NULL)
			{
				return;
			}
			
			// Get the	position
			$NewPos = $this->DirToLoc($Actor->Wx, $Actor->Wy, $Info['Dir']);	
			
			// Get the target block
			$TargetBlock = $Actor->MyMap->GetBlockAtLoc($NewPos['x'], $NewPos['y']);
			
			if ($TargetBlock == NULL)
			{
				return;
			}

			// Get the ID of the first item on the block
			$ItemID = $TargetBlock->TopItemIs();			
			if ($ItemID == NULL)
			{
				return;
			}
			
			// Tell the map to to remove the object
			$RemovedFromMap = $Actor->MyMap->RemoveObject($ItemID);			
			if ($RemovedFromMap == false)
			{
				return;
			}
			
			// Give it to the user
			$Item = $Data['World']->GetObject($ItemID);
			if ($Item == NULL)
			{
				return;
			}
			
			// Ensure the object knows its new onwer
			$Item->SetOwner($Actor->ID);
			
			// Tell the owner
			$Actor->AddItemByID($ItemID);			
		}		
	}
?>