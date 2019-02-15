<?php
	class Wall extends Block
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->WalkSpeed = 0;
			$this->RID = 9;
			$this->SeeThrough = false;
		}
		
		// ******************************
		public function IsSeeThrough()
		{
			return false;
		}
	}	
?>