<?php
	class KillPlayer extends AI
	{
		private $CurrentTargetID;
		
		// ******************************
		function __construct($ID)
		{
			parent::__construct($ID);		
		}
		
		// ******************************
		public function Process()
		{
			global $Data;
			
			$StillActive = false;			
			$Actor = $Data['World']->GetObject($this->OwnerID);
			
			// Target identified, or not
			if ($this->CurrentTargetID === NULL)
			{
				// No target as yet, then look for one
				$this->CurrentTargetID = $Actor->LookForA("Player");
				$this->NextThink = FrameForTimeMS(100); 
				
				// If the NPC has locked onto a target
				// then this objective is still active
				if ($this->CurrentTargetID != NULL)
				{
					$StillActive = true;
					//LogMe("Got a target");
				}
			}
			else
			{
				// Target aquired				
				$TargetObject = $Data['World']->GetObject($this->CurrentTargetID);
				if ($TargetObject == NULL)
				{
					// Target is no longer valid
					$this->CurrentTargetID = NULL;
					$StillActive = false;
				}
				else
				{
					// Work out the distance to the player.
					// This is needed to work out what action
					// will take next.  Too far, then the target
					// is dropped.  Too close and the target is
					// attacked.  Not close enough, then the
					// target is moved apon.
					$Distance = $Actor->DistanceTo($TargetObject->Wx, $TargetObject->Wy);
					$Dir = $Actor->LocToDir($TargetObject->Wx, $TargetObject->Wy);
					
					if ($Distance <= $Actor->GetAttackRange())
					{
						// Attack
						$Action['Action'] = "AttackDir";
						$Action['Dir'] = $Dir;
						$Action['Damage'] = $Actor->GetWeaponDamage();
						$Action['SelfID'] = $this->OwnerID;			
						$Data['AIF']->Act($Action);
						$StillActive = true;
					}
					else if ($Distance > 5)
					{
						// Target is lost
						$this->CurrentTargetID = NULL;
						$StillActive = false;
						$Actor->SetNextThink(FrameForTimeMS(1000));
						//LogMe("TargetLost");
					}
					else
					{
						// Move
						$Action['Action'] = "Move";
						$Action['Dir'] = $Dir;
						$Action['SelfID'] = $this->OwnerID;			
						$Data['AIF']->Act($Action);
						$StillActive = true;
					}					
				}
			}				
			return $StillActive;
		}		
	}
?>