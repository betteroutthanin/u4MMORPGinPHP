<?php
	class Forest extends Block
	{
		// ******************************
		function __construct()
		{
			parent::__construct();	
			
			$this->WalkSpeed = 0.25;
			$this->RID = 1;
			$this->SeeThrough = false;
		}
		
		// ******************************
		public function IsSeeThrough()
		{
			return false;
		}
	}	
?>