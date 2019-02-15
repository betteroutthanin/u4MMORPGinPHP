<?php
	class Tiackable extends Block
	{
		// When the block can next think about
		// taking an action.  Unit is server frames
		// and is compared against the current server
		// frame.
		protected $NextThink;
		
		// ******************************
		function __construct()
		{
			parent::__construct();			
		}
		
		// ******************************
		public function Tick()
		{					
		}
		
		// ******************************		
		public function IsTickable()
		{
			return true;
		}
		
		// ******************************
		public function SetNextThink($NextThink)
		{
			$this->NextThink = $NextThink;
		}
	}	
?>