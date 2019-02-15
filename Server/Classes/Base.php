<?php
	// Base of all other classes
	class Base
	{
		// ******************************
		function __construct()
		{
			// Base class, no need to call the constructor
		}		

		// ******************************
		// This will allow all protected varibles
		// to become read only.
		public function __get($NameOfVar)
		{					
			return $this->$NameOfVar;
		}
		
		// ******************************
		protected function ProcessDataString($DataString)
		{						
			// Class name is needed for later
			$ClassName = get_class($this);	
			
			// One Line at a time
			$LineArray = explode(":", $DataString);
			foreach ($LineArray as $Line)
			{
				// Clean up the line, helps to find blank lines
				$Line = trim($Line);
				
				// Ignore if the line is blank
				if ($Line == "")
				{
					continue;
				}				
				
				// The Line must have a "=" sign in it
				if (strpos($Line, "=") === false)
				{					
					continue;
				}
				
				// The Line must be valid
				// Break up and clean up
				$Parts = explode("=", $Line);				
				$Parts[0] = trim($Parts[0]);
				$Parts[1] = trim($Parts[1]);
								
				// Test to see if the object has a varible of that name
				if (property_exists($ClassName, $Parts[0]) === true)
				{
					$this->$Parts[0] = $Parts[1];
				}
				else
				{
					//LogMe("While loading ".$FileName.", failed to find property ->".$Parts[0]);					
				}				
			}// End of Line Loop
		}
	}
?>