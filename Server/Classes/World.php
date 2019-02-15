<?php
	class World extends Base
	{		
		// Master List		
		private $Objects;	
		private $Maps;
		
		// Reference Lists
		private $Livings;
		private $Items;
		
		// Keep track of available object IDs
		private $IDSeqHoles;
		private $IDSeqNext;
		
		// ******************************
		function __construct()
		{
			global $Data;
			parent::__construct();
			
			// Need to create the sequence data for IDs
			$this->SetUpIDSequences();
			LogMe("Next in Sequence = ".$this->IDSeqNext);
			LogMe("Hole in Sequence array = ");
			LogMe(print_r($this->IDSeqHoles, true));
			
			// Master List for all game objects
			// Must be a high speed indexed look up
			$this->Objects = new SplFixedArray($Data['Config']['MaxObjects']);
			
			// Master List of all maps
			$this->Maps = array();
			
			// The worlds action interface.  This will 
			// need to be registered globally so it can
			// be called from any where
			$this->AIF = new ActionInterface();
			$Data['AIF'] = $this->AIF;			
						
			LogMe("World Object created");					
		}
		
		// ******************************
		// Called once per fly back, is calls other
		// tick functions to keep the server running
		public function Tick()
		{		
			// Tick the living objects
			$LivingCount = count($this->Livings);
			if ($LivingCount > 0)
			{			
				foreach ($this->Livings as $ObjectID)
				{				
					$Object = $this->GetObject($ObjectID);
					$Object->Tick();
					unset($Object);
				}
			}
			
			// Tick all the maps
			$MapCount = count($this->Maps);
			if ($MapCount > 0)
			{
				foreach ($this->Maps as $Map)
				{
					$Map->Tick();
				}
			}
			
			
			// Injector
			$this->BackDoorInjector();
			
			// Save all the objects
			static $SaveCounter = 500;			
			if ($SaveCounter > 120)
			{
				$this->SaveAllObjectsToDataBase();
				$SaveCounter = 0;
			}
			$SaveCounter++;			
		}		

		// ******************************
		// Populates the world with data from the database.
		// Creates maps, blocks and objects.  It also injects
		// objects into the world so that they sit in the
		// correct map and block buckets
		public function BuildWorld()
		{
			global $Data;
			
			// Load All Maps
			$Query = "SELECT * FROM maps";			
			$MapsToLoad = $Data['DataBase']->GetThis($Query);
			if ($MapsToLoad != false)
			{
				foreach ($MapsToLoad as $DBD)
				{
					$NewMap = new Map();
					$NewMap->Populate($DBD);
					$this->Maps[$NewMap->Name] = $NewMap;					
				}
			}
			
			// Load All Objects - ignored Players and Owned	objects
			// Players and thier owned objects will be loaded when
			// the player connects
			$Query = "SELECT * FROM objects WHERE type!='Player' and ownerid=-1;";			
			$ObjectsToLoad = $Data['DataBase']->GetThis($Query);			
			if ($ObjectsToLoad != false)
			{
				foreach ($ObjectsToLoad as $DBD)
				{
					// Create the blank object of the target type
					$NewObject = new $DBD['type']();
					
					// Tell the object to self poputlate from the DatBbase
					$NewObject->Populate($DBD['id']);
					
					// Add it to the master list
					$this->Objects[$NewObject->ID] = $NewObject;
										
					// Finally inject into the world space
					$this->InjectObjectIntoWorld($NewObject->ID);
				}			
			}
		}
		
		// ******************************
		// ARGS: x,y are world coords
		// RETURN: Map object that holds the cords
		public function GetMapByLoc($x, $y)
		{
			$Map = NULL;
			foreach ($this->Maps as $MapToTest)
			{
				if ($MapToTest->DoIOwnTheseCoords($x, $y) == true)
				{
					$Map = $MapToTest;
					break;
				}
			}			
			return $Map;
		}		
		
		// ******************************
		// Object Foundery
		// All things related to objects
		// ******************************
		
		// ******************************
		// Returns the Object Object for the request ID
		// ARGS: ID of the object needed
		// RETURN:  Object that matches the ID
		public function GetObject($ID)
		{
			global $Data;
			
			// Is it in range?
			if ($ID > $Data['Config']['MaxObjects'])
			{
				LogMe("Requested Object ID is out of range ->".$ID);
				return NULL;
			}
			
			// Get the object
			$Object = $this->Objects[$ID];
			if ($Object == NULL)
			{
				LogMe("Requested Object ID is NULL ->".$ID);
			}
			
			// All good, return the reference to the object
			return $Object;			
		}
		
		// ******************************
		// Push an object into the correct map/block buckets.
		// It used ID, and assumes that the object is in the
		// World Master Object List
		// ARGS: ID, ID of the object that needs to be pushed		
		public function InjectObjectIntoWorld($ID)
		{
			LogMe("Injecting Object into the world -> ".$ID);
			
			$Object = $this->GetObject($ID);
			if ($Object == NULL)
			{
				// todo - add some error handling
				return;
			}
						
			// Living or Item, the world needs to split 
			// these up so it can thrash through the 
			// Living list for during the tick cycle
			if ($Object->IsLiving() == true)
			{
				$this->Livings[$Object->ID] = $Object->ID;
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
			
			// Objects can be placed into 1 of two bucket type
			// A map (including blocks) or a player.  The player
			// Must be loaded to take on the object.  An OwnerID
			// of -1 indicates that the object is map bound
			if ($Object->OwnerID == -1)
			{
				// Object is map bound
				$Map = $this->GetMapByLoc($Object->Wx, $Object->Wy);
				if ($Map == NULL)
				{
					// todo - add some error handling.
					// might even need to return T/F						
					return;
				}
				
				$Map->AddObject($Object->ID);
			}
			else
			{
				// Object is player bound
				$OwnerObject = $this->GetObject($Object->OwnerID);
				if ($OwnerObject == NULL)
				{
					// Todo - see map bound adds
					// Crap this object was added to the world
					// but the the owner is not in the world
					LogMe("Crap this object was added to the world but the the owner is not in the world");
					exit();
				}
				
				// Tell the player (object) to take the item
				$OwnerObject->AddItemByID($ID);
			}			
		}
		
		// ******************************
		// Saves the object to the Database.
		// Remove it from map(and block) or owner.
		// Remove it Living/Items List
		// Remove from Master List
		// ARGS: ID, ID of the object that needs removed				
		public function UnloadObject($ID)
		{
			global $Data;
			
			LogMe("World is unloading object -> ".$ID);

			// Get the object
			$Object = $this->GetObject($ID);
			if ($Object == NULL)
			{
				// Object doesn't exist
				return;
			}
			
			// Save the object to the DataBase
			$Object->Save();

			// Take it from who ever owns it
			// Map or Player
			if ($Object->OwnerID == -1)
			{
				// World removeral
				$Object->MyMap->RemoveObject($Object->ID);				
			}
			else
			{
				// Player removeral
				$Owner = $this->GetObject($Object->OwnerID);
				if ($Owner != NULL)
				{
					$Owner->RemoveItemByID($Object->ID);
				}
				else				
				{
					// todo - Player object is not loaded
					// why is an object they own loaded
					// add some error handling for this
				}
			}
			
			// Remove from world reference lists
			if ($Object->IsLiving() == true)
			{
				unset($this->Livings[$Object->ID]);
			}
			else if ($Object->IsItem() == true)
			{
				unset($this->Items[$Object->ID]);
			}
			
			// Remove from the master list
			unset($this->Objects[$Object->ID]);			
			
			// Unset the object and hope for death
			unset($Object);
		}
		
		// ******************************
		// Total object death.  Even destroys the Database version
		// ARGS: ID, ID of the object to be destroyed				
		public function DestroyObject($ID)
		{
			global $Data;
			
			// Total death
			LogMe("Destroying object -> ".$ID);
			
			// Get the object
			$Object = $this->GetObject($ID);
			if ($Object == NULL)
			{
				return;
			}			
					
			// Remove it from the Map and block
			$Object->MyMap->RemoveObject($ID);
						
			// Remove it from the World/living or Item
			if ($Object->IsLiving() == true)
			{
				unset($this->Livings[$Object->ID]);
			}
			else if ($Object->IsItem() == true)
			{
				unset($this->Items[$Object->ID]);
			}
			
			// Remove it from the Master List
			unset($this->Objects[$ID]);
			
			// Remove it from the DataBase
			$Delete =  "Delete from objects where";				
			$Delete .= " id = '".$ID."'";				
			$Data['DataBase']->DoThis($Delete);
			
			// Since the object is destroyed we should
			// recycle the ID
			array_push($this->IDSeqHoles, $ID);
			
			// Unset the object, hope for death			
			unset($Object);
		}
				
		// ******************************
		// Create a new object of Type and adds it 
		// to the master List
		// ARGS:  Type, the class type of the object
		// RETURN: ID of the object
		public function CreateNewObject($Type)
		{
			// Create the object
			$NewObject = new $Type();
			
			// Allocate it an ID
			$ID = $this->GetNextSeqID();
			$NewObject->SetID($ID);
			
			// Add it to the master list
			$this->Objects[$NewObject->ID] = $NewObject;
			
			// Tell the requester the ID
			return $NewObject->ID;			
		}
				
		// ******************************
		// Saves all objects in the master list into
		// the database
		public function SaveAllObjectsToDataBase()
		{
			foreach ($this->Objects as $Object)
			{
				if ($Object != NULL)
				{
					$Object->Save();
				}
			}
		}

		// ******************************
		// Loads the target player from the database and
		// adds them to the world.  Also any object that
		// the player owns is also loaded and added.
		// ARGS: PlayerName, name of the player to be loaded
		// RETURN:  ID of the players object
		public function ConnectPlayer($PlayerName)
		{
			global $Data;
			
			// Get the player's object id from the database					
			$Query = "SELECT * FROM players WHERE name='".$PlayerName."'";			
			$PlayerData = $Data['DataBase']->GetThis($Query);			
			if ($PlayerData == NULL)
			{
				return NULL;
			}
			
			// Create the empty Player obeject
			$PlayerObject = new Player();
				
			// Tell the object to self poputlate from that DataBase
			$PlayerObject->Populate($PlayerData[0]['objectid']);
			
			// Force the players name
			$PlayerObject->SetName($PlayerName);
			
			// Add it to the master list
			$this->Objects[$PlayerObject->ID] = $PlayerObject;
									
			// Finally inject into the world space
			$this->InjectObjectIntoWorld($PlayerObject->ID);
			
			// Load my Stuff :)
			$Query = "SELECT * FROM objects WHERE ownerid='".$PlayerObject->ID."'";			
			$PlayerItems = $Data['DataBase']->GetThis($Query);			
			
			// Process the items if there is any
			if ($PlayerItems != NULL)
			{
				foreach ($PlayerItems as $DBD)
				{
					// Create the blank object
					$NewObject = new $DBD['type']();
					
					// Tell the object to self poputlate
					// from that DatBbase
					$NewObject->Populate($DBD['id']);
					
					// Add it to the master list
					$this->Objects[$NewObject->ID] = $NewObject;
										
					// Finally inject into the world space
					$this->InjectObjectIntoWorld($NewObject->ID);
					
					unset($NewObject);					
				}
			}
			
			return $PlayerObject->ID;
		}
		
		// ******************************
		// Removes a player from the world.  It will
		// remove all the players objects as well.
		// ARGS: ID, ID of the player to be removed	
		public function DisConnectPlayerID($ID)
		{
			global $Data;
			
			// Get the Object
			$Player = $this->GetObject($ID);
			if ($Player == NULL)
			{
				return;
			}
					
			// Unload all the players objects
			if (count($Player->Items) > 0)
			{
				LogMe("Telling the world to unload players objects");
				foreach ($Player->Items as $ItemID)
				{
					$this->UnloadObject($ItemID);
				}				
			}			
					
			// Unload the player, this will also
			// remove the object from the world
			// structures
			$this->UnloadObject($Player->ID);
						
			// delete the local copy of the player object
			unset($Player);
		}
		
		// ******************************
		// Sets up the two key parts that keep track
		// available sequences for object creation.
		private function SetUpIDSequences()
		{
			global $Data;
						
			$Last = 0;
			$this->IDSeqHoles = array();
			$this->IDSeqNext = 0;
			
			// Get all the objects
			$Query = "SELECT id FROM objects ORDER BY id DESC;";			
			$ObjectIDs = $Data['DataBase']->GetThis($Query);			
			
			// If there is no objects
			if ($ObjectIDs == false)
			{
				return;
			}
			
			// Next in sequnce will always be the
			// the one next after the highest
			$this->IDSeqNext = $ObjectIDs[0]['id'] + 1;
			$Last =  $ObjectIDs[0]['id'];
			unset($ObjectIDs[0]);			
						
			foreach ($ObjectIDs as $ID)
			{							
				// Look for a gap
				$Gap = $Last - $ID['id'];				
				
				// If the gap is larger than one, then
				// there is some holes in the system.
				// Fill them up
				if ($Gap > 1)
				{					
					for ($GapID = $ID['id'] + 1; $GapID < $Last ; $GapID++)
					{						
						array_push($this->IDSeqHoles, $GapID);
					}
				}
				
				$Last = $ID['id'];				
			}
			
			// The above code will only detect hole between
			// entries, it will not find gap to position 0
			$Gap = $Last - 0;	
			if ($Gap > 0)
			{					
				for ($GapID = 0; $GapID < $Last ; $GapID++)
				{						
					array_push($this->IDSeqHoles, $GapID);
				}
			}
		}
		
		// ******************************	
		// Gets and the next available object ID, will
		// also maintain the two key systems for keeping
		// track of the avaible IDs
		// RETURN: The allocated ID
		public function GetNextSeqID()
		{
			$ID = 0;
			if (count($this->IDSeqHoles) > 0)
			{
				//There are still numbers in the holes
				$ID = array_pop($this->IDSeqHoles);
			}
			else
			{
				// Get the next in Seqence
				$ID = $this->IDSeqNext;
				$this->IDSeqNext++;
			}
			
			return $ID;
		}
		
		// ******************************		
		private function BackDoorInjector()
		{
			$FileName = "Data/Injector/doit.php";
			
			// Get the injection file
			if (file_exists($FileName) == true)
			{
				LogMe(get_class($this)." - Injector Started");
				include($FileName);
				unlink($FileName);
				LogMe(get_class($this)." - Injector Ended");
			}
		}
		
		// ******************************		
		private function HealthCheck()
		{
			foreach ($this->Objects as $Object)
			{
				if ($Object != NULL)
				{					
					$TempMap = $Object->MyMap;
					$TempBlock = $Object->MyBlock;
					
					$Object->MyMap = NULL;
					$Object->MyBlock = NULL;
					
					debug_zval_dump($Object);
					
					$Object->MyMap = $TempMap;
					$Object->MyBlock = $TempBlock;
				}				
			}			
			exit;
		}		
	}
?>