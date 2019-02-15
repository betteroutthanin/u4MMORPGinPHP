<?php	
	class GameObject extends Base
	{	
		// Taken directly from DB fields
		protected $ID;		// Unique Key
		protected $Type;	// Help define the class of the object
		protected $OwnerID;
		
		// From Data String
		protected $Wx;
		protected $Wy;		
		protected $Name;
		
		// Helpers for rapid navigation
		// These can be read and write, there
		// is no real need to protect these
		public $MyMap;
		public $MyBlock;
		
		// Values that are to be saved into the
		// data string on save
		protected $SaveList;
		
		protected $RID;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			// Defaults			
			$this->Name = get_class($this);
			$this->Type = get_class($this);
			$this->OwnerID = -1;
			
			$this->SaveList = array();
			array_push($this->SaveList, "Wx");
			array_push($this->SaveList, "Wy");
						
			$this->GOB("Created");
		}
		
		// ******************************
		public function Tick()
		{			
		}
		
		// ******************************
		protected function GOB($Message)
		{
			$Header = "";
			$Header .= "ID = ".$this->ID;
			$Header .= ": Type = ".$this->Type;
			$Header .= ": Name = ".$this->Name;
			$Header .= ": Wx = ".$this->Wx;
			$Header .= ": Wy = ".$this->Wy;
			
			$Header .= " == ";
			
			LogMe($Header.$Message);
		}

		// ******************************
		public function Populate($ID)
		{	
			global $Data;		
			
			$this->GOB("Starting Population of ".get_class($this));
			
			$Query = "SELECT * FROM objects WHERE id=".$ID.";";			
			$this->GOB($Query);
			$DBD = $Data['DataBase']->GetThis($Query);
			
			if ($DBD == NULL)
			{				
				$this->GOB("Failed to find object in data base");
				return;
			}
						
			// Object will be populated from a Database data array
			// Ensure the ID is set through the SetID function.
			
			$this->SetID($DBD[0]['id']);
			$this->Type = $DBD[0]['type'];
			$this->OwnerID = $DBD[0]['ownerid'];
			
			// The rest of the data will need
			// to populated from the data string
			$this->ProcessDataString($DBD[0]['data']);
			
			$this->GOB("Populated");
		}
		
		// ******************************
		public function Save()
		{
			global $Data;
			
			//$this->GOB("Starting save to DataBase");
			
			// Build the data string
			$DataString = "";
			foreach ($this->SaveList as $ItemName)
			{
				$DataString .= $ItemName;
				$DataString .= " = ";
				$DataString .= $this->$ItemName;
				$DataString .= " : ";
			}
			
			// Update or insert?
			$Query = "SELECT * FROM objects WHERE id=".$this->ID.";";			
			//$this->GOB($Query);
			$DBD = $Data['DataBase']->GetThis($Query);
			
			if ($DBD == NULL)
			{
				// Insert time
				$Insert  = "INSERT INTO"; 
				$Insert .= " objects(id, type, ownerid, data)";
				$Insert .= " values('".$this->ID."', '".$this->Type."', '".$this->OwnerID."', '".$DataString."')";
				//$this->GOB($Insert);
				
				$Data['DataBase']->DoThis($Insert);				
			}
			else
			{
				// Update time
				//$this->GOB("Object allready exists");
				$Update =  "Update objects set";				
				$Update .= " type = '".$this->Type."', ";
				$Update .= " ownerid = '".$this->OwnerID."', ";
				$Update .= " data = '".$DataString."'";
				$Update .= " where id = '".$this->ID."'";
				//$this->GOB($Update);
				
				$Data['DataBase']->DoThis($Update);								
			}

			$this->GOB("Saved to DataBase");
		}
		
		// ******************************
		public function IsLiving()
		{
			return is_subclass_of($this, "Living");
		}
		
		// ******************************
		public function IsItem()
		{
			return is_subclass_of($this, "Item");
		}
		
		// ******************************
		public function SetPosition($Nx, $Ny)
		{			
			$this->Wx = $Nx;
			$this->Wy = $Ny;
			$this->GOB("SetPosition");			
		}

		// ******************************
		public function SetID($ID)
		{
			$this->ID = $ID;
			$this->GOB("SetID");			
			$this->Save();
		}

		// ******************************
		public function SetName($Name)
		{
			$this->Name = $Name;
			$this->GOB("SetName");			
			$this->Save();
		}
		
		// ******************************
		public function SetOwner($ID)
		{
			$this->OwnerID = $ID;
			$this->GOB("SetOwner");			
			$this->Save();
		}
	}
?>