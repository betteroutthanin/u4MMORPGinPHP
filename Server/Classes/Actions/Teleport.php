<?php
	class Teleport extends Action
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->MustHave[0] = "LocX";
			$this->MustHave[1] = "LocY";
			$this->MustHave[2] = "SelfID";	

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

			// do we need need change maps
			$TargetMap = $Data['World']->GetMapByLoc($Info['LocX'], $Info['LocY']);			
			
			if ($TargetMap == NULL)
			{
				LogMe("No Map found");
			}
			
			if ($TargetMap != $Actor->MyMap)
			{
				// A map change is needed				
				// Can we move into the new home
				$NewBlock = $TargetMap->GetBlockAtLoc($Info['LocX'], $Info['LocY']);				
				if ($NewBlock == NULL)
				{
					return;					
				}
				
				if ($NewBlock->CanObjectEnter($Actor->ID) == false)
				{
					return;
				}
				
				// Ok we are good to go			
				// Remove the target from the current map
				$Actor->MyMap->RemoveObject($Actor->ID);
				
				// reset the actors position
				$Actor->SetPosition($Info['LocX'], $Info['LocY']);
				
				// Tell the target map to pull it down
				$TargetMap->AddObject($Actor->ID);				
			}
			else
			{
				// No map change is needed
				// Alot of this is borrowed from Move
				$NewPos['x'] = $Info['LocX'];
				$NewPos['y'] = $Info['LocY'];
				
				$TargetBlock = $Actor->MyMap->GetBlockAtLoc($NewPos['x'], $NewPos['y']);
				if ($TargetBlock == NULL)
				{
					return;
				}
				
				// Can the actor more into the block
				$CanActorEnter = $TargetBlock->CanObjectEnter($Actor->ID);
				if ($CanActorEnter == false)
				{
					return;
				}
				
				// Remove from the old
				$Removed = $Actor->MyBlock->RemoveObject($Actor->ID);
				if ($Removed == false)
				{
					return;
				}			
				
				// Add to the new
				$Added = $TargetBlock->AddObject($Actor->ID);
				if ($Added == false)
				{
					return;
				}
				
				// Set position
				$Actor->SetPosition($NewPos['x'], $NewPos['y']);
			}
		}		
	}
?>