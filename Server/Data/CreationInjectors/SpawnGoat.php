<?php
	$x = 102;
	$y = 91;
	$Block = $this->GetMapByLoc($x, $y)->GetBlockAtLoc($x, $y);
	if ($Block->Living == NULL)
	{
		$GoatID = $this->CreateNewObject("Goat");
		$Goat = $this->GetObject($GoatID);
		$Goat->SetPosition($x, $y);	
		$Goat->Save();	
		$this->InjectObjectIntoWorld($GoatID);	
	}	
?>