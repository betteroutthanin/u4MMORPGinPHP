<?php
	class Living extends GameObject
	{
		protected $View;
	
		// From DataBase
		protected $HP;						
		
		// When the object can next think about
		// taking an action.  Unit is server frames
		// and is compared against the current server
		// frame.
		protected $NextThink;

		// Radius of seeing - how far a thing
		// can see when looking for things
		// such as an enermy
		protected $LookRadius;	

		// Attack Details
		// These are not saved into the database
		// These need to be populated at creation
		// or when a player equips a weapon/armour.		
		protected $AttackRange;
		protected $BaseDamage;
		protected $RandDamage;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			array_push($this->SaveList, "HP");						
			
			$this->NextThink = 0;
			$this->LookRadius = 3;
			$this->View = new View();
		}		
		
		// ******************************
		public function Tick()
		{
			parent::Tick();
			
			// The view needs an ID of the parent object.
			// THe problem is that some there is two methods
			// to set the ID, instead of trying to capture all
			// the calls to set the ID is it easier just to sniff
			// the ID and pass it onto the view
			$this->View->SetOwner($this->ID);
			
			// All living objects need to create a view.  This
			// view is snap shot of what the object can see
			// it is used to create renders for the client
			// and help the AI system make descisions.
			$this->View->BuildView();			
			
			// !!! It is critical that death is dealt with
			// in an objects own tick.  This will force the
			// object to self remove itself from the world
			// via the DealWithDeath function.  If deal with
			// death is call via an external (to this class)
			// function chain then the object will not be
			// able to detangle itself from the refernce map.
			// Many fucking hours of messing around and reading
			// where spent on this.
			
			// Are we dead, then exit this sweet world
			if ($this->HP < 1)
			{				
				$this->GOB("is dead");
				$this->DealWithDeath();								
			}
		}
		
		// ******************************
		public function TakeDamage($Damage)
		{			
			$this->HP = $this->HP - $Damage;			
			$this->GOB("got attacked, HP remaining = ".$this->HP);			
		}

		// ******************************
		public function DealWithDeath()
		{
			$this->GOB("Object has died, but this is being handled at a upper level");
		}		
		
		// ******************************
		public function SetNextThink($NextThink)
		{
			$this->NextThink = $NextThink;
		}
		
		// ******************************
		public function GetAttackRange()
		{
			return $this->AttackRange;
		}
		
		// ******************************
		public function GetWeaponDamage()
		{
			$Damage = $this->BaseDamage + rand(0, $this->RandDamage);
			return $Damage;
		}
		
		// ******************************
		//      AI Stuff
		// ******************************
		
		// ******************************
		public function LocToDir($Tx, $Ty)
		{
			// Todo - fix this, some times it gets stuck
			// returns -1
		
			$DiffX = $Tx - $this->Wx;
			$DiffY = $Ty - $this->Wy;
			 
			$Radians = atan2($DiffY, $DiffX);
			
			$Angle = $Radians * (180/M_PI);
			
			// Deal with the wrap around
			if ($Angle < -157.5)
			{
				$Angle = $Angle + 360;
			}
			
			// center = 0
			$Dir[6] = array(-22.5, 22.5);
			$Dir[3] = array(22.5, 67.5);
			$Dir[2] = array(67.5, 112.5);
			$Dir[1] = array(112.5, 157.5);
			$Dir[4] = array(157.5, 202.5);
			$Dir[7] = array(-157.5, -112.5);
			$Dir[8] = array(-122.5, -67.5);
			$Dir[9] = array(-67.5, -22.5);
			
			$DirKey = -1;
			
			foreach ($Dir as $Direction=>$Parts)
			{
				if (($Angle >= $Parts[0]) && ($Angle <= $Parts[1]))
				{
					$DirKey = $Direction;
					break;
				}
			}				
			
			if ($DirKey == -1)
			{
				LogMe("Stuck angle -> ".$Angle);
			}
			
			return $DirKey;
		}
		
		// ******************************
		public function DistanceTo($Wx, $Wy)
		{
			$x = $Wx - $this->Wx;
			$y = $Wy - $this->Wy;
			
			$x = pow($x, 2);
			$y = pow($y, 2);
			
			$Total = $x + $y;			
			
			$Distance = floor(sqrt($Total));
			
			return $Distance;			
		}
		
		// ******************************
		public function LookForA($Type)
		{
			global $Data;
			
			// Exit checks
			if (count($this->View->Livings) == 0)
			{
				return;
			}			
			
			// Loop and look
			// Todo, get the distance on these suckers
			foreach ($this->View->Livings as $LivingID => $LivingData)
			{
				$LivingObject = $Data['World']->GetObject($LivingID);
				
				if (get_class($LivingObject) == $Type)
				{
					return $LivingID;
				}				
			}			
		}		
	}
?>