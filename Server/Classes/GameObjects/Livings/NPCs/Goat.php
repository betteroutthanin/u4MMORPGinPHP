<?php
	class Goat extends NPC
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			$this->Loot[0] = array("Item"=>"Dagger", "Chance"=>"50");
			$this->Loot[1] = array("Item"=>"Axe", "Chance"=>"50");
			$this->HP = 400;
			$this->RID = 100;
		}
		
		// ******************************
		function __destruct()
		{
			$this->GOB("!!!! destruct() !!!!!");			
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
				$this->Objectives[0] = (new Wonder($this->ID));
			}
		}
	}
?>