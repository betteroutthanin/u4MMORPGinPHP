<?php
	class Weapon extends Item
	{
		protected $Damage;
		protected $Range;
		// ******************************
		function __construct()
		{
			parent::__construct();			
			$this->Range = 1;
		}	
	}
?>