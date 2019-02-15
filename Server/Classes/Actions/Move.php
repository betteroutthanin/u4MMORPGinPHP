<?php
	class Move extends Action
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
			// Get the Object
			$Actor = $Data['World']->GetObject($Info['SelfID']);			
			if ($Actor == NULL)
			{
				return;
			}			
			
			// Get the	position
			$NewPos = $this->DirToLoc($Actor->Wx, $Actor->Wy, $Info['Dir']);			
			
			// Get the Target block
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
			
			// Next think
			// Todo - work this out so that it delays based on terrain
			$BaseDelay = 1 - $TargetBlock->WalkSpeed;
			$Actor->SetNextThink($Data['ServerFrame'] + ($BaseDelay * 5) + 2);
		}		
	}
?>