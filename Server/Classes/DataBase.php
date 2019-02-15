<?php	
	class DataBase
	{
		private $Connection;		
		
		// ******************************
		function __construct()
		{			
			global $Data;
			
			// Connect to the DataBase
			$this->Connection = pg_connect($Data['Config']['DataBaseConnect']);
			
			if ($this->Connection == FALSE)
			{
				LogMe("Connection to Data Base failed -> ".$Data['Config']['DataBaseConnect']);
				exit;
			}			
			
			LogMe("DataBase Connection made");
		}
		
		// ******************************
		function __destruct()
		{		
			// Close the connection
			pg_close($this->Connection);
		}	
		
		// ******************************		
		public function GetThis($Query)
		{			
			$result = pg_query($this->Connection, $Query);
			$ResultAsArray = pg_fetch_all($result);
			pg_free_result($result);			
			return $ResultAsArray;						
		}
		
		// ******************************		
		public function DoThis($Query)
		{			
			$result = pg_query($this->Connection, $Query);			
			return;
		}
	}	
?>