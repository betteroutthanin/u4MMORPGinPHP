<?php
	class Wonder extends AI
	{		
		// ******************************
		function __construct($ID)
		{
			parent::__construct($ID);		
		}
		
		// ******************************
		public function Process()
		{
			global $Data;
			
			$StillActive = true;			
			$Actor = $Data['World']->GetObject($this->OwnerID);
			
			$DirArray = array(8,9,6,3,2,1,4,7);
			$Action['Action'] = "Move";
			$Action['Dir'] = $DirArray[rand(0, count($DirArray) - 1)];
			$Action['SelfID'] = $this->OwnerID;			
			$Data['AIF']->Act($Action);
						
			return $StillActive;
		}		
	}
?>