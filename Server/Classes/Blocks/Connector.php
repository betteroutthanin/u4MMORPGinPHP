<?php
	class Connector extends Block
	{
		protected $DestX;
		protected $DestY;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->WalkSpeed = 1;			
			$this->RID = 11;
		}
		
		// ******************************		
		public function IsConnector()
		{
			return true;
		}
	}	
?>