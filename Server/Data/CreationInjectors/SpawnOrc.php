<?php
	$x = 163;
	$y = 171;
	$Block = $this->GetMapByLoc($x, $y)->GetBlockAtLoc($x, $y);
	if ($Block->Living == NULL)
	{
		$ID = $this->CreateNewObject("Orc");
		$Object = $this->GetObject($ID);
		$Object->SetPosition($x, $y);	
		$Object->Save();	
		$this->InjectObjectIntoWorld($ID);	
	}	
?>