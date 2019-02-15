<?php
	class AttackDir extends Action
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->MustHave[0] = "Dir";
			$this->MustHave[1] = "SelfID";	
			$this->MustHave[2] = "Damage";

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
						
			// Get the the living object on that block
			$TargetBlock = $Actor->MyMap->GetBlockAtLoc($NewPos['x'], $NewPos['y']);						
			if ($TargetBlock == NULL)
			{
				return;
			}
			
			// Het the living ID for this block
			$TargetObjectID = $TargetBlock->Living;
			if ($TargetObjectID == NULL)
			{
				return;				
			}
			
			// Get the object so damage can be applied
			$TargetObject = $Data['World']->GetObject($TargetObjectID);
			if ($TargetObject == NULL)
			{
				return;
			}		
			
			// Hit it
			$TargetObject->TakeDamage($Info['Damage']);
			LogMe($Actor->ID." attacked ".$TargetObjectID." for ".$Info['Damage']." damage.");
			
			// Next think
			// Todo - delay based on weapons
			$Actor->SetNextThink($Data['ServerFrame'] + 10);
		}		
	}
?>