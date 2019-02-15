<?php
	class ReturnTo extends AI
	{
		private $ReturnX;
		private $ReturnY;		
		
		// ******************************
		function __construct($ID, $Wx, $Wy)
		{
			parent::__construct($ID);
			
			$this->ReturnX = $Wx;
			$this->ReturnY = $Wy;			
		}
		
		// ******************************
		public function Process()
		{
			global $Data;
			
			$StillActive = true;

			// Get the actor
			$Actor = $Data['World']->GetObject($this->OwnerID);
			
			// Are we on our spot?
			if (($Actor->Wx == $this->ReturnX) && ($Actor->Wy == $this->ReturnY))
			{
				$StillActive = false;
				$this->NextThink = FrameForTimeMS(1000); 
			}
			else
			{
				// Move towards the return spot
				$Dir = $Actor->LocToDir($this->ReturnX, $this->ReturnY); 
				$Action['Action'] = "Move";
				$Action['Dir'] = $Dir;
				$Action['SelfID'] = $this->OwnerID;			
				$Data['AIF']->Act($Action);
				$StillActive = true;
				
				// Next think will be handled by the move
			}			
						
			return $StillActive;
		}		
	}
?>