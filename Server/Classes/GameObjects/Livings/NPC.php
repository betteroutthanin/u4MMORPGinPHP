<?php
	class NPC extends Living
	{
		protected $Loot;
		protected $Objectives;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			// Bucket for the loot
			// Loot array should be populated like this
			// array("Item"=>"Dagger", "Chance"=>"50");
			$this->Loot = array();
			
			// Objects
			$this->Objectives = array();
		}

		// ******************************
		public function Tick()
		{
			global $Data;
			
			parent::Tick();				
			
			// Is it time to think?
			if ($Data['ServerFrame'] < $this->NextThink)
			{
				return;
			}		
			
			// Set objectives.  This will ensure that
			// all the objects are put in place if 
			// they are not already.  This has to be
			// done here instead of the contructor.
			// This is due to the fact that the ID of
			// the object is not known at the point of
			// construction.
			$this->CreateObjectives();
			
			// loop through all the objects and act on them in order 
			if (count($this->Objectives) > 0)
			{
				foreach($this->Objectives as $Objective)
				{
					$StillActive = $Objective->Process();
					
					if ($StillActive == true)
					{
						break;
					}
					
					// Else, proceed to the next Objective
				}
			}		
		}

		// ******************************
		public function DealWithDeath()
		{
			global $Data;
			
			// Drop some loot?
			$Count = count($this->Loot);
			if ($Count > 0)
			{	
				// What will it drop
				$Pos = rand(0, $Count - 1);
				
				// The details of the item are needed
				$ItemDetails = $this->Loot[$Pos];				
				$RollFromDrop = rand(1, 100);
				
				if ($RollFromDrop < $ItemDetails['Chance'])
				{
					$ItemType = $ItemDetails['Item'];
					$ItemID = $Data['World']->CreateNewObject($ItemType);
					$Item = $Data['World']->GetObject($ItemID);
					$Item->SetPosition($this->Wx, $this->Wy);
					$Data['World']-> InjectObjectIntoWorld($ItemID);
				}				
			}
			
			// Don't call the parent
			// NPC has died, for now we will destroy it
			
			$Data['World']->DestroyObject($this->ID);
		}		
	}
?>