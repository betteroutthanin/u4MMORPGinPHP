<?php
	class Axe extends Weapon
	{
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->Damage = 10;
			$this->RID = 201;
		}

		// ******************************
		function __destruct()
		{
			$this->GOB("!!!! destruct() !!!!!");			
		}		
	}
?>