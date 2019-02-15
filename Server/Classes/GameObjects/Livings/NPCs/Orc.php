<?php
	class Orc extends NPC
	{		
		// ******************************
		function __construct()
		{
			parent::__construct();			
			
			// Orc Specific thing
			
			$this->Loot[0] = array("Item"=>"Dagger", "Chance"=>"10");
			$this->Loot[1] = array("Item"=>"Axe", "Chance"=>"10");
			
			$this->HP = 400;
			$this->RID = 101;

			// Some combat details
			$this->AttackRange = 1;
			$this->BaseDamage = 10;
			$this->RandDamage = 5;
		}
		
		// ******************************
		function __destruct()
		{			
			$this->GOB("!!!! ".get_class()." removed from the world !!!!!");
		}
		
		// ******************************
		public function Tick()
		{			
			parent::Tick();			
		}
		
		// ******************************
		public function CreateObjectives()
		{
			if (count($this->Objectives) == 0)
			{				
				$this->Objectives[0] = (new KillPlayer($this->ID));
				$this->Objectives[1] = (new ReturnTo($this->ID, $this->Wx, $this->Wy));
			}
		}
	}
?>