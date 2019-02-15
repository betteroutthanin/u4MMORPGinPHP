<?php
	class Block extends Base
	{
		// Reference Lists
			protected $Living;		// ID of the one living thing on this square
			protected $Items;		// ID list of things
		
		// Properties
			protected $Type;					
			protected $WalkSpeed;	// 0 = can't be passed 1 = full speed
			private $SeeThrough;	// T/F, private to ensure a function is used to read it
			protected $RID;			// render ID, maps to the client image set
			
		// Its location in the world
			protected $Wx;
			protected $Wy;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->RID = 1;
			$this->WalkSpeed = 1;						
			$this->SeeThrough = true;
			$this->Items = array();
			$this->Living = NULL;
			
			$this->Wx = -1;
			$this->Wy = -1;			
		}
		
		// ******************************		
		public function Populate()
		{
			// Gets any custom settings from the database
			// for this block.  The block will needs its
			// position set before this is called, this 
			// is due to the reason that they position
			// will be used as a look up in the database

			global $Data;			
			
			//return;
					
			if ($this->Wx == -1)
			{
				LogMe("Block needs to have its position set before populating");
				exit;
			}
			
			$Query = "SELECT * FROM blocks WHERE wx=".$this->Wx." and wy=".$this->Wy.";";			
			$BlockToLoad = $Data['DataBase']->GetThis($Query);			
			if ($BlockToLoad == false)
			{
				return;
			}
			
			// Get the goodies
			$this->ProcessDataString($BlockToLoad[0]['data']);			
		}

		// ******************************
		public function SetPosition($Nx, $Ny)
		{			
			$this->Wx = $Nx;
			$this->Wy = $Ny;			
		}

		// ******************************
		public function AddObject($ID)
		{
			global $Data;
			
			$Object = $Data['World']->GetObject($ID);
			if ($Object == NULL)
			{
				// todo - add some error handling
				return false;
			}			
			
			if ($Object->IsLiving() == true)
			{
				// Any one else here ?
				if ($this->Living != NULL)
				{
					// Crap
					LogMe("Some one else is in this block -> ".$Object->ID);
					LogMe("     And that someone is -> ".$this->Living);
					return false;
				}				
				$this->Living = $Object->ID;
			}
			else if ($Object->IsItem() == true)
			{
				$this->Items[$Object->ID] = $Object->ID;
			}
			else
			{
				// Well WTF is it?
				// Todo add some code to handle this error
			}
			
			$Object->MyBlock = $this;
			LogMe("Object Added to block ID -> ".$Object->ID . "  Block Type = ".get_class($this));
			
			return true;
		}
		
		// ******************************
		public function RemoveObject($ID)
		{
			global $Data;
			
			$Object = $Data['World']->GetObject($ID);
			if ($Object == NULL)
			{				
				return false;
			}
			
			$Object->MyBlock = NULL;
			
			// Living?
			if ($Object->IsLiving() == true)
			{
				$this->Living = NULL;
			}
			else if ($Object->IsItem() == true)
			{
				unset($this->Items[$Object->ID]);
			}
			else
			{
				LogMe("Nothing to remove");
				// Well WTF is it?
				// Todo add some code to handle this error
			}
			
			//LogMe("Object Removed from block ID -> ".$Object->ID);
			return true;
		}
		
		// ******************************
		// Questions and helpers
		// ******************************
		
		// ******************************
		public function IsSeeThrough()
		{
			return $this->SeeThrough;
		}
		
		// ******************************
		public function TopItemIs()
		{
			$ItemCount = count($this->Items);
			if ($ItemCount == 0)
			{
				return NULL;
			}
			
			// Get the first item
			$Key = array_keys($this->Items);
						
			return $this->Items[$Key[0]];			
		}
		
		// ******************************
		public function CanItemBeDropped()
		{
			if ($this->WalkSpeed <= 0)
			{				
				return false;
			}
			
			//else
			
			return true;			
		}
		
		// ******************************		
		public function IsConnector()
		{
			return false;
		}
		
		// ******************************		
		public function IsTickable()
		{
			return false;
		}
		
		// ******************************		
		public function CanObjectEnter($ID)
		{
			// Tests to see if a living object can
			// enter this block
			
			global $Data;
			
			$Object = $Data['World']->GetObject($ID);
			if ($Object == NULL)
			{
				LogMe("CanObjectEnter($ID) - Object was NULL");
				return false;
			}
			
			// These rules only apply to living things
			if ($Object->IsLiving() == false)
			{
				LogMe("CanObjectEnter($ID) - Object is not living");
				return true;
			}

			// Basic rules
			// Some else is home?
			if ($this->Living != NULL)
			{
				LogMe("CanObjectEnter($ID) - Living already on block");				
				return false;
			}
			
			// Its a freaking wall
			if ($this->WalkSpeed == 0)
			{
				LogMe("CanObjectEnter($ID) - Is not walkable");
				return false;
			}
			
			// Otherwise the block sees
			// no good reason to accept it
			return true;
		}
	}
?>