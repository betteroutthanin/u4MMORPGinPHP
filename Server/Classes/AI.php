<?php
	class AI extends Base
	{
		protected $OwnerID;
		// ******************************
		function __construct($ID)
		{
			parent::__construct();			
			
			$this->OwnerID = $ID;
		}
	}
?>