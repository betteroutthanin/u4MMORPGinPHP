<?php
	class NPCTemplate extends NPC
	{		
		// ******************************
		function __construct()
		{
			parent::__construct();			
		}
		
		// ******************************
		function __destruct()
		{			
		}
		
		// ******************************
		public function Tick()
		{
			parent::Tick();			
		}

		// ******************************
		public function DealWithDeath()
		{		
			// Always call this as the last thing
			// to be done.  Living::DealWithDeath
			// will remove the object from the game
			parent::DealWithDeath();			
		}
	}
?>