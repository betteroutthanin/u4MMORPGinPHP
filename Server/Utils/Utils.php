<?php
	// ******************************
	function LogMe($Message, $Level = 0, $Clear = false)
	{
	
		$Level = 6;
		$LogFileName = "Log.txt";
		// Clear the log???
		if ($Clear == true)
		{
			file_put_contents($LogFileName, "Start of Log"."\n");
		}
		
		// show the message on screen
		if ($Level > 3)
		{
			echo $Message."\n";
		}
		
		// Write it to disk
		file_put_contents($LogFileName, $Message."\n", FILE_APPEND);
		
	}	
	
	// ******************************
	function ScanFolder(&$FileList, $dir = '.', $exclude = array( 'cgi-bin', '.', '..' ))
	{
		$objects = 	new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($dir), 
					RecursiveIteratorIterator::SELF_FIRST);

		foreach($objects as $name => $object)
		{						
			if (is_file($object->getPathname()) ) 
			{
				//$PathData = pathinfo( $object->getPathname());
				//array_push($FileList, $PathData['basename']);				
				array_push($FileList, $object->getPathname());				
			}			
		}
	}
	
	// ******************************
	function ScanAndInclude($PathToScan)
	{
		$FileList = array();
		ScanFolder($FileList, $PathToScan);
		
		foreach($FileList as $File)
		{			
			include_once($File);
			LogMe("Loading Class -> ".$File);
		}
	}
	
	// ******************************
	function ScanAndStrip($PathToScan)
	{
		$FileList = array();
		$StrippedFileList = array();
		ScanFolder($FileList, $PathToScan);
		
		foreach($FileList as $File)
		{			
			$FileData = pathinfo($File);
			array_push($StrippedFileList, $FileData['filename']);
		}
		
		return $StrippedFileList;
	}

	// ******************************
	function FrameForTimeMS($Time)
	{
		global $Data;
				
		$FrameCount = round($Time / $Data['Config']['TickTimeMS'], 0);		
		
		return $FrameCount + $Data['ServerFrame'];
	}
?>