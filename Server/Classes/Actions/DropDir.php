<?php
	class DropDir extends Action
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
			
			// exit if they have no items
			if (count($Actor->Items) == 0)
			{
				return;
			}
			
			// Can the item be dropped onto the target
			// target block, punch out if not possible
			$NewPos = $this->DirToLoc($Actor->Wx, $Actor->Wy, $Info['Dir']);				
			$TargetBlock = $Actor->MyMap->GetBlockAtLoc($NewPos['x'], $NewPos['y']);			
			if ($TargetBlock == NULL)
			{
				return;
			}
			
			if ($TargetBlock->CanItemBeDropped() == false)
			{
				LogMe("Items can't be dropped there");
				return;
			}
			
			// Todo - make this work a little better
			// the action may need to specify the ID
			// of the item they are intending to drop			
			$ItemKeys = array_keys($Actor->Items);
			$ItemID = $Actor->Items[$ItemKeys[0]];
			
			// Take from the player
			$Actor->RemoveItemByID($ItemID);
			
			// Get the Item as an Object
			$Item = $Data['World']->GetObject($ItemID);
			
			// Stip the owners
			$Item->SetOwner(-1);
			
			// New position
			$Item->SetPosition($NewPos['x'], $NewPos['y']);
			
			// Inject it into the world
			$Data['World']->InjectObjectIntoWorld($ItemID);				
		}		
	}
?>