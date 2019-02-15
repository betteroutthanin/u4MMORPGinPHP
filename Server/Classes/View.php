<?php
	class View extends Base
	{
		protected $OwnerID;		
		protected $ViewRadius;
		protected $Blocks;	
		protected $Livings;

		private $ViewSizeX;
		private $ViewSizeY;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->OwnerID = NULL;
			$this->ViewRadius = 7 ;
			
			$this->ViewSizeX = ($this->ViewRadius * 2) + 1;
			$this->ViewSizeY = ($this->ViewRadius * 2) + 1;
			
			// High speed array access
			$Blocks = new SplFixedArray($this->ViewSizeX * $this->ViewSizeY);			
			
			// This takes a lot of grunt.  The cull tree will only
			// be built once.  Please see the contents on the function
			// the better undertand how this works
			$this->BuildCullingTree();

			$this->Livings = array();
		}
		
		// ******************************
		public function SetOwner($ID)
		{
			$this->OwnerID = $ID;			
		}

		// ******************************
		public function BuildViewForced()
		{
			// In some cases a view of an object will need
			// todo 
		}
		
		// ******************************
		public function BuildView()
		{
			global $Data;					
			
			// Can't build a view without a owner ID
			if ($this->OwnerID == NULL)
			{
				return;
			}			
			
			// The actor is needed
			$Actor = $Data['World']->GetObject($this->OwnerID);
			if ($Actor == NULL)
			{
				return;
			}			
			
			// Get the boundary of the view
			$CX = $Actor->Wx;			
			$CY = $Actor->Wy;			
			
			$SVX = $CX - $this->ViewRadius;
			$SVY = $CY - $this->ViewRadius;			
			$EVX = $CX + $this->ViewRadius;
			$EVY = $CY + $this->ViewRadius;
			
			// Build the Unfiltered View
			$Index = 0;
			for ($y = $SVY; $y <= $EVY; $y++)
			{				
				for ($x = $SVX; $x <= $EVX; $x++)
				{
					$Block = $Actor->MyMap->GetBlockAtLoc($x, $y);					
					$UnfilteredView[$Index] = $Block;					
					$Index++;
				} // End x loop	
			} // End x loop
			
			// Clear the view
			$Index = 0;
			for ($y = $SVY; $y <= $EVY; $y++)
			{				
				for ($x = $SVX; $x <= $EVX; $x++)
				{					
					$this->Blocks[$Index] = NULL;					
					$Index++;
				} // End x loop	
			} // End x loop
			
			// The view will contain a list of IDs of living
			// objects in the view.  This list is rebuilt
			// every time the view is refreshed			
			$this->Livings = array();
			
			// Populate via the node tree
			$this->ProcessNode($Data['CT'][0], $UnfilteredView);
		}
		
		// ******************************
		private function ProcessNode(&$Node, &$UnFilteredView)
		{
			// Convert the x/y into an array index
			$ix = $Node['x'] + $this->ViewRadius;
			$iy = $Node['y'] + $this->ViewRadius;
			
			$Index = $ix + ($iy * $this->ViewSizeX);
			
			// Add the current block to the view
			$Block = $UnFilteredView[$Index];
			$this->Blocks[$Index] = $Block;
			
			// Look for any living objects and add them to the list
			// Don't add the views owne, could get ugly
			if (($Block->Living != NULL) && ($Block->Living != $this->OwnerID))
			{
				$this->Livings[$Block->Living] = $Block->Living;				
			}
		
			$ForceFollow = false;
			// Allows follow nodes if the center block is non see through
			// just imporves the view
			if (($Node['x'] == 0) && ($Node['y'] == 0))
			{
				$ForceFollow = true;
			}
			
			if (($Block->IsSeeThrough() === true) || ($ForceFollow === true))
			{			
				foreach ($Node['Children'] as $Child)
				{
					$this->ProcessNode($Child, $UnFilteredView);
				}
			}
		}
		
		// ******************************
		public function Render()
		{
			global $Data;
			
			$ViewSizeX = ($this->ViewRadius * 2) + 1;
			$ViewSizeY = ($this->ViewRadius * 2) + 1;
			
			$Block_Buffer = "";
			$Item_Buffer = "";
			$Living_Buffer = "";
			
			$Index = 0;
			for ($y = 0; $y < $ViewSizeY; $y++)
			{
				for ($x = 0; $x < $ViewSizeX; $x++)
				{
					$Block = $this->Blocks[$Index];
					if ($Block != NULL)
					{						
						$BlockType = get_class($Block);					
						
						// Block
						$Block_Buffer .= pack("s", $Block->RID);						
						
						// Living						
						if ($Block->Living != NULL)
						{							
							$LivingObject = $Data['World']->GetObject($Block->Living);
							$Living_Buffer .= pack("s", $LivingObject->RID);						
						}
						else
						{						
							$Living_Buffer .= pack("s", 0);						
						}
						
						if (count($Block->Items) > 0)
						{
							$ItemID = $Block->TopItemIs();
							$Item = $Data['World']->GetObject($ItemID);
							$Item_Buffer .= pack("s", $Item->RID);								
						}
						else
						{
							$Item_Buffer .= pack("s", 0);						
						}						
					}
					else
					{
						$Block_Buffer  .= pack("s", 0);
						$Living_Buffer .= pack("s", 0);
						$Item_Buffer   .= pack("s", 0);						
					}
					$Index++;
				}
			}
			
			// Combined and write out the buffers
			return $EndBuffer = $Block_Buffer.$Living_Buffer.$Item_Buffer;	
		} // end function Render		
		
		// ******************************
		// 		Node tree building
		// ******************************
		
		// ******************************
		private function BuildCullingTree()
		{
			global $Data;
			
			// CT tree only needs to be built once
			// for the entire life of the server.
			// it will be done by this the first
			// instance of this class and stored in
			// the global $Data array			
			static $TreeBuilt = false;			
			if ($TreeBuilt == true)
			{
				return;
			}
						
			// *** Get a list of blocks and their parents loc ***
			// Start to build the tree
			$CX = 0;
			$CY = 0;
			$SVX = $CX - $this->ViewRadius;
			$SVY = $CY - $this->ViewRadius;			
			$EVX = $CX + $this->ViewRadius;
			$EVY = $CY + $this->ViewRadius;						
			$Index = 0;
			$ParentList = array();
			for ($y = $SVY; $y <= $EVY; $y++)
			{				
				for ($x = $SVX; $x <= $EVX; $x++)
				{
					
					if (($x == 0) && ($y == 0))
					{
					}
					else
					{
						$ParentList[$Index]["x"] = $x; 
						$ParentList[$Index]["y"] = $y; 
						$ParentList[$Index]["Parent"] = $this->WhoIsMyParent($x, $y);						
					}					
					$Index++;
				} // End x loop	
			} // End x loop
			//print_r($ParentList);		
			
			// *** Build Node Tree ***
			$NodeArray = array();			
			$this->BuildNodeTree(0, 0, $NodeArray, $ParentList);			
			
			// Save it globally
			$Data['CT'] = $NodeArray;			
			
			// Ensure that this is only called once per
			// server life cycle
			$TreeBuilt = true;
		}
		
		// ******************************
		private function BuildNodeTree($x, $y, &$NodeArray, &$ParentList)
		{			
			// Start a new node
			$Node = array();
			$Node['x'] = $x;
			$Node['y'] = $y;
			$Node['Children'] = array();
						
			while (true)
			{
				$Child = $this->LookForAChild($x, $y, $ParentList);				
				if ($Child === false)
				{
					break;
				}				
				
				// Trigger a new search
				$this->BuildNodeTree($Child['x'], $Child['y'], $Node['Children'], $ParentList);				
			}			
			array_push($NodeArray, $Node);			
		}
		
		// ******************************
		private function LookForAChild($x, $y, &$ParentList)
		{
			$Child = false;
			
			foreach ($ParentList as $Key => $Entry)
			{
				if (($Entry['Parent']['x'] == $x) && ($Entry['Parent']['y'] == $y))
				{
					$Child['x'] = $Entry['x'];
					$Child['y'] = $Entry['y'];					
					unset($ParentList[$Key]);
					break;
				}
			}			
			return $Child;
		}
		
		// ******************************
		private function WhoIsMyParent($x, $y)
		{
			// We want to start in the center of the block
			$LenX = 0 - ($x + 0.5);
			$LenY = 0 - ($y + 0.5);
								
			$VectorLen = sqrt(($LenX * $LenX) + ($LenY * $LenY));
			$LoopLen = 0.7;
			
			$InclineX = $LenX / $VectorLen;
			$InclineY = $LenY / $VectorLen;
								
			$px = $x + ($LoopLen * $InclineX);
			$py = $y + ($LoopLen * $InclineY);
			
			$px = ((int) $px);
			$py = ((int) $py);
			
			$Results["x"] = $px; 
			$Results["y"] = $py; 
			
			return $Results;			
		}		
	}	
?>