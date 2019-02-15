<?php
	class Server extends Base
	{
		// Contains
		private $World;			// Single Master object

		// Data base connector
		private $DataBase;
		
		// List of all connection index by player name
		private $Connections;
		private $DisConnections;
		
		// Server Stuff		
		private $Port;
		private $Socket;
		
		// ******************************
		function __construct()
		{
			parent::__construct();		

			$this->Connections = array();
			$this->DisConnections = array();
			
			LogMe("Server Object created");
		}
	
		// ******************************
		public function BootServer()
		{
			global $Data;
			
			LogMe("Boot Server Starting . . ");
			
			// Server is needed by disconnections
			$Data['Server'] = $this;
			
			// Create the database connection
			$this->DataBase = new DataBase();
			$Data['DataBase'] = $this->DataBase;
			
			$this->InitNetwork();
			
			// At this point there is only one world
			$this->World = new World();
			
			// Several other class will need access to the world
			// Do this before the world is built, just incase
			// some calls by other classes during creation need
			// access to the world
			$Data['World'] = $this->World;

			// Load the World
			$this->World->BuildWorld();		
									
			LogMe(". . . Boot Server Ended");
			
			// Clean up the injector
			if (file_exists("Data/Injector/doit.php") == true)
			{
				unlink("Data/Injector/doit.php");
			}
		}
		
		// ******************************
		public function TickLoop()
		{
			global $Data;
			$StartLoopTime;
			$EndLoopTime;
						
			LogMe("Entering Main Loop");
			while (1)
			{				
				$StartLoopTime = microtime();
				// Server stuff start
				// ====================================				
					//LogMe("--------------> Start of Server Frame ".$Data['ServerFrame']);
					
					// Process any incoming data
					$this->ProcessConnections();					
					
					// World Tick
					$this->World->Tick();

					// Process any pending connects
					$this->ProcessPendingConnections();
					$this->ProcessPendingDisConnections();
					
					// Render Connections
					$this->RenderConnections();					
										
					$Data['ServerFrame']++;
				// ====================================
				// Server stuff end
				$EndLoopTime = microtime();

				// Sleep for a bit, based on server load
				// usleep is in 1 millionths of a second
				$TimeTakenMS = ($EndLoopTime - $StartLoopTime) * 1000;	
				$SleepTimeMS = $Data['Config']['TickTimeMS'] - $TimeTakenMS;				
				
				// Some time sleep time is negative? This is due
				// to the fact that the frame took longer than
				// the allocated time.
				if ($SleepTimeMS > 0)
				{
					usleep($SleepTimeMS * 1000);				
				}
				else
				{
					LogMe("Frame took to long -> ". $SleepTimeMS);
				}				
			}// End Main Loop of TickLoop
		}
		
		// ******************************
		private function ProcessConnections()
		{			
			if (count($this->Connections) == 0)
			{
				return;
			}
			
			// Calc
			foreach ($this->Connections as $Connection)
			{
				$ConnectionGood = $Connection->Process();
				if ($ConnectionGood == false)
				{
					LogMe("Pushing into disconnection list");
					array_push($this->DisConnections, $Connection);
				}
			}					
		}
		
		// ******************************
		private function RenderConnections()
		{			
			if (count($this->Connections) == 0)
			{
				return;
			}			
			
			// Render
			foreach ($this->Connections as $Connection)
			{
				$Connection->Render();
			}			
		}
		
		// ******************************
		private function InitNetwork()
		{
			$this->Port = 10001;
			$this->Socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);			
			if ($this->Socket === false)
			{
				LogMe("Socket Fail");
				LogMe(socket_strerror(socket_last_error($this->Socket)));
				exit;
			}
			
			$BindResult = socket_bind($this->Socket, "127.0.0.1", $this->Port);
			if ($BindResult === false)
			{
				LogMe("Socket Fail");
				LogMe(socket_strerror(socket_last_error($this->Socket)));
				exit;
			}
			
			$ListenResult = socket_listen($this->Socket, 5);
			if ($ListenResult === false)
			{
				LogMe("Socket Fail");
				LogMe(socket_strerror(socket_last_error($this->Socket)));
				exit;
			}
			
			socket_set_nonblock($this->Socket);
			LogMe("Socket Created");
		}

		// ******************************
		private function ProcessPendingConnections()
		{
			global $Data;
					
			$read = array($this->Socket);
			$write = NULL;
			$except = NULL;
			
			// An new connections
			$Ammount = socket_select($read, $write, $except, 0);
			
			if ($Ammount > 0)
			{
				LogMe("Got One");
								
				$NewConnection = new Connection();				
				$NewConnection->SetSocket(socket_accept($this->Socket));
				array_push($this->Connections, $NewConnection);
			}	
			
			// Any disconnections
			foreach ($this->Connections as $ConnectionObject)
			{
				// todo - fix this
				//$ConnectionObject->TestForDisconnected();
			}
		}
		
		// ******************************
		private function ProcessPendingDisConnections()
		{
			// Nothing to disconnect?
			if (count($this->DisConnections) == 0)
			{				
				return;
			}
			
			// Any valid connects?
			if (count($this->Connections) == 0)
			{				
				return;
			}
			
			// Loop through all the pending Disconnects
			// Close the connections in the list
			foreach ($this->DisConnections as $DisconnectKey => $Connection)
			{
				LogMe("Got a disconnect ->".$Connection->OwnerID);				
				$ConnectionKey = array_search($Connection, $this->Connections);								
				LogMe("ConnectionKey ->".$ConnectionKey);
				
				if ($ConnectionKey !== false)
				{				
					// In some cases the player object my have not been created
					// and therefore no need to remove the player
					if ($Connection->OwnerID !== NULL)
					{
						// Tell the world to disconnect the player
						$this->World->DisConnectPlayerID($Connection->OwnerID);
					}
					
					// Remove the object from the Disconnection list
					unset($this->DisConnections[$DisconnectKey]);
					
					// Remove the connection from the master list
					unset($this->Connections[$ConnectionKey]);
					
					// Final clean up
					unset($Connection);
				}			
			}			
		}		
		
		// ******************************
		public function DisconnectID($ID)
		{
			foreach ($this->Connections as $Connection)
			{
				if ($Connection->OwnerID == $ID)
				{					
					array_push($this->DisConnections, $Connection);
					return;
				}
			}
		}
	}
?>