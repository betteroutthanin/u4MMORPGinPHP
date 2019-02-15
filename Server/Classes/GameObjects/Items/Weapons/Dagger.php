<?php
	class Dagger extends Weapon
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->Damage = 10;
			$this->RID = 200;
		}	
		
		// ******************************
		function __destruct()
		{
			$this->GOB("!!!! destruct() !!!!!");			
		}	
	}
?>