<?php
	class Action extends Base
	{
		protected $MustHave;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->MustHave = array();
		}
		
		// ******************************
		public function PrepAndCheck($Info)
		{			
			foreach ($this->MustHave as $Required)
			{
				if (array_key_exists($Required, $Info) === false)
				{
					// Info array was passed but is missing data
					LogMe("Info array was passed but is missing data -> ". $Required);
					return;
				}
			}

			// Proceed to process
			$this->Process($Info);
		}
		
		// ******************************
		protected function DirToLoc($Cx, $Cy, $Dir)
		{			
			// Work out what the new location will be			
			$Nx = $Cx;
			$Ny = $Cy;
			
			switch ($Dir)
			{
				case "8";
					$Ny--;
				break;
				
				case "9";
					$Ny--;
					$Nx++;
				break;
				
				case "6";					
					$Nx++;
				break;
				
				case "3";
					$Ny++;
					$Nx++;
				break;
				
				case "2";
					$Ny++;					
				break;
				
				case "1";
					$Ny++;					
					$Nx--;
				break;
				
				case "4";					
					$Nx--;
				break;
				
				case "7";
					$Ny--;					
					$Nx--;
				break;
			}
			
			$Result['x'] = $Nx;
			$Result['y'] = $Ny;
			
			return $Result;
		}
	}
?>