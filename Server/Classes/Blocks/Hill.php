<?php
	class Hill extends Block
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->WalkSpeed = 0.25;
			$this->RID = 3;
		}
	}	
?>