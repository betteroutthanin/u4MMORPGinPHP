<?php
	class Spawner extends Tiackable
	{		
		// ******************************
		function __construct()
		{
			parent::__construct();	
			
			$this->RID = 2;			
		}
		
		// ******************************
		public function Tick()
		{					
			global $Data;
			
			parent::Tick();
			
			// Is it time to think?
			if ($Data['ServerFrame'] < $this->NextThink)
			{
				return;
			}
			
			// Requardless of what happens ensure the next think is set
			$this->SetNextThink(FrameForTimeMS(60000));
			
			// Don't proceed if there is something on the block
			if ($this->Living != NULL)
			{
				return;
			}
			
			// Spawn an Orc
			$ID = $Data['World']->CreateNewObject("Orc");
			$Object = $Data['World']->GetObject($ID);
			$Object->SetPosition($this->Wx, $this->Wy);	
			$Object->Save();	
			$Data['World']->InjectObjectIntoWorld($ID);
			
			LogMe("Block goes tick");
		}
	}	
?>