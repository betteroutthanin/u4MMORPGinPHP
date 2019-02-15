<?php
	class Map extends Base
	{
		// Data Field
		protected $Name;

		// Data String
		protected $StartX;
		protected $StartY;
		protected $EndX;
		protected $EndY;		
			
		// Calulated
		protected $SizeX;
		protected $SizeY;
			
		// Master List
		private $Blocks;

		// Reference list of blocks that need to be ticked
		private $TickableBlocks;
				
		// ******************************
		function __construct()
		{
			parent::__construct();

			$this->TickableBlocks = array();
		}
		
		// ******************************		
		public function Tick()
		{
			$BlockCount = count($this->TickableBlocks);			
			if ($BlockCount > 0)
			{
				foreach ($this->TickableBlocks as $Block)
				{
					$Block->Tick();
				}
			}
		}
		
		// ******************************
		public function AddObject($ID)
		{
			global $Data;
			
			$Object = $Data['World']->GetObject($ID);
			if ($Object == NULL)
			{				
				return false;
			}
			
			// Get the block
			$Block = $this->GetBlockAtLoc($Object->Wx, $Object->Wy);
			
			if ($Block == NULL)
			{				
				return false;				
			}		
			
			// Pass it onto the block
			$Result = $Block->AddObject($ID);			
			if ($Result == false)
			{
				// todo - need to handle the failure to add the object
				// to a surrounding block
				return false;				
			}	

			// Keep track of what map we are on
			$Object->MyMap = $this;						
			return true;			
		}
		
		// ******************************
		public function RemoveObject($ID)
		{
			global $Data;
			
			$Object = $Data['World']->GetObject($ID);
			if ($Object == NULL)
			{
				// todo - add some error handling
				LogMe("Failed to get object from world -> ".$ID);
				return false;
			}
			
			// Remove it from the block
			$RemovedFromBlock = $Object->MyBlock->RemoveObject($ID);			
			if ($RemovedFromBlock == false)
			{
				return false;
			}
			
			// Remove it from the map
			$Object->MyMap = NULL;
			return true;
		}			
				
		// ******************************
		// Questions and Helpers
		// ******************************
		
		// ******************************
		public function DoIOwnTheseCoords($x, $y)
		{
			if (($y >= $this->StartY) && ($y <= $this->EndY))
			{
				if (($x >= $this->StartX) && ($x <= $this->EndX))
				{
					return true;
				}
			}
			
			return false;
		}
		
		// ******************************
		public function GetBlockAtLoc($WorldX, $WorldY)
		{
			if ($this->DoIOwnTheseCoords($WorldX, $WorldY) == false)
			{
				return NULL;
			}		
			
			$OffSet = $this->BuildBlockArrayOffset($WorldX, $WorldY);			
			return $this->Blocks[$OffSet];
		}
		
		// ******************************
		private function BuildBlockArrayOffset($WorldX, $WorldY)
		{
			$x = $WorldX - $this->StartX;
			$y = $WorldY - $this->StartY;
			$OffSet = $x + ($y * $this->SizeX);
			return $OffSet;
		}
		
		// ******************************
		public function Populate($DBD)
		{
			// Object will be populated from a 
			// Database data array
			$this->Name = $DBD['name'];
			
			LogMe("Loading Map -> ".$this->Name.". . .");
						
			// The rest of the data will need
			// to populated from the data string
			$this->ProcessDataString($DBD['data']);
			
			// The size may not be part of the map, so it will
			// need to be calculated
			$this->SizeX = ($this->EndX - $this->StartX) + 1;
			$this->SizeY = ($this->EndY - $this->StartY) + 1;
						
			// Create all the freaking blocks			
			$this->Blocks = new SplFixedArray($this->SizeX * $this->SizeY);
			$HeaderData = $this->GetHeaderData($this->Name);			
			$HeaderPos = 0;
			
			$BlockType = array();
			$BlockType[0]  = "Blank";
			$BlockType[1]  = "Forest";
			$BlockType[2]  = "Grass";
			$BlockType[3]  = "Hill";
			$BlockType[4]  = "Mountain";
			$BlockType[5]  = "Road";
			$BlockType[6]  = "Scrub";
			$BlockType[7]  = "Swamp";
			$BlockType[8]  = "Town";			
			$BlockType[9]  = "Wall";			
			$BlockType[10] = "Water";			
			$BlockType[11] = "Connector";			
			$BlockType[12] = "BridgeEW";			
			$BlockType[13] = "BridgeNS";			
			$BlockType[14] = "Rock";			
			$BlockType[15] = "Spawner";
			
			for ($y = $this->StartY; $y <= $this->EndY; $y++)
			{
				for ($x = $this->StartX; $x <= $this->EndX; $x++)
				{
					// Create the block
					$Type = $HeaderData[$HeaderPos];					
					$NewBlock = new $BlockType[$Type]();
					$NewBlock->SetPosition($x, $y);
					$NewBlock->Populate();
					
					// Todo add support for custom block settings
					// Maybe do this in the database											

					// Add the block to the array
					// NOTE the array is set no relative to the world cords
					$Offset = $this->BuildBlockArrayOffset($x, $y);
					$this->Blocks[$Offset] = $NewBlock;	
					
					// if the block is tickable then add it to the list
					if ($this->Blocks[$Offset]->IsTickable() == true)
					{
						array_push($this->TickableBlocks, $this->Blocks[$Offset]);						
					}

					$HeaderPos++;
				}
			}
			
			// Last but not least tell the freaking world
			LogMe(" . . . Map -> ".$this->Name." loaded");
		}
		
		// ******************************
		private function GetHeaderData($FileName)
		{			
			$FullPath = "Data/MapHeaders/".$FileName.".h";
			$Buffer = file_get_contents($FullPath);
			
			$TopMarket = "static char header_data[] = {";
			$TopPos = strpos($Buffer, $TopMarket);
			$TopPos = $TopPos + strlen($TopMarket);			
			$BotMarket = "};";
			$BotPos = strpos($Buffer, $BotMarket, $TopPos);			
			
			$Buffer = substr($Buffer, $TopPos, $BotPos - $TopPos);
			
			$Parts = explode(",", $Buffer);
						
			// Trim them
			$Count = 0;
			foreach ($Parts as $Key=>$Part)
			{
				$Parts[$Key] = trim($Part);
				$Count++;
			}
			LogMe("Found ".$Count." blocks");
			return $Parts;
		}				
	}
?>