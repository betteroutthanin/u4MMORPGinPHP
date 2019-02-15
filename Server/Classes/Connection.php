<?php
	class Connection extends Base
	{
		protected $OwnerID;		// ID of the object
		protected $PlayerName;	// Name of the player		
		private $MsgSocket;		// Socket back to the client
		private $LastResponse;	// As server frames
		
		private $State;			// State of the connection
		private $InBuffer;		// Buffer to store the incoming data from the client
				
		// ******************************
		function __construct()
		{			
			parent::__construct();
			
			$this->State = "WaitingForLoggin";
			$this->OwnerID = NULL;
			$this->InBuffer = "";
		}
		
		// ******************************
		function __destruct()
		{
			LogMe("!!! Connect closed -> ".$this->OwnerID);		
			
			socket_close($this->MsgSocket);			
		}
		
		// ******************************
		public function BindConnection($PlayerID)
		{			
			// Bind the object ID to this class
			$this->OwnerID = $PlayerID;			
		}
		
		// ******************************
		public function Process()
		{
			global $Data;
			
			// Read an new data in
			$Read = array($this->MsgSocket);
			$Write = NULL;
			$Exceptions = NULL;
					
			$Changes = socket_select($Read, $Write, $Exceptions, 0);
			
			// Some sort of crazy error
			if ($Changes === false)
			{
				// Fat error
				// todo handle properly
				LogMe("Connection->Process() - Changes === false");
				LogMe(socket_strerror(socket_last_error($this->MsgSocket)));
				return true;
			}			
			
			// And changes?
			if ($Changes == 0)
			{
				//LogMe("Connection->Process() - Changes == 0");
				//LogMe(socket_strerror(socket_last_error($this->MsgSocket)));
				return true;
			}
			
			$TempReadBuffer = @socket_read($this->MsgSocket, 2048, PHP_BINARY_READ);
			
			if ($TempReadBuffer === "")
			{
				LogMe("Disconnected");
				return false;
			}			
			
			$this->InBuffer .= $TempReadBuffer;			
		
			switch ($this->State)
			{
				case "WaitingForLoggin":					
					$Parts = explode("=", trim($this->InBuffer));
					if ($Parts[0] == "Loggin")
					{
						$PlayerName = $Parts[1];
						$this->InBuffer = "";
						
						$PlayerID = $Data['World']->ConnectPlayer($PlayerName);
						if ($PlayerID != NULL)
						{
							$this->BindConnection($PlayerID);
							$this->State = "Connected";
							$this->PlayerName = $PlayerName;
							
							LogMe($PlayerName . " Connected !!!!!");
							
							// Send message to player
							$ConnectString = "Connected\0";
							socket_write($this->MsgSocket, $ConnectString, strlen($ConnectString));
						}
						else
						{
							return false;
						}
						
						// Clear the buffer
						$this->InBuffer = "";
					}
				break;
				
				// Get data and build Actions
				case "Connected":
					$this->ProcessBuffer();
					$this->InBuffer = "";
				break;
			}

			return true;
		}
		
		// ******************************
		private function ProcessBuffer()
		{
			global $Data;
		
			$AsLines = explode("\n", $this->InBuffer);
						
			$Action = array();
			$Action['SelfID'] = $this->OwnerID;
			
			// Build up the action array
			foreach ($AsLines as $Line)
			{
				if ($Line == "")
				{
					continue;
				}
				
				$LineParts = explode("=", $Line);
				
				if (count($LineParts)!= 2)
				{
					return;
				}
				
				$Key = trim($LineParts[0]);
				$Value = trim($LineParts[1]);				
				$Action[$Key] = $Value;				
			}					
			
			// Pass the action array onto the player.
			// They player will process this in thier
			// next tick.
			$Player = $Data['World']->GetObject($this->OwnerID);
			$Player->SetPendingAction($Action);					
		}

		// ******************************
		public function Render()
		{
			global $Data;
			
			if ($this->OwnerID == NULL)
			{
				return;
			}
			
			if ($this->State != "Connected")
			{
				return;
			}
			
			// Get the onwers object
			$Actor = $Data['World']->GetObject($this->OwnerID);
			if ($Actor == NULL)
			{
				return;
			}
			
			$Buffer = $Actor->View->Render();
			socket_write($this->MsgSocket, $Buffer, strlen($Buffer));
		}
		
		// ******************************
		public function SetSocket($Socket)
		{
			$this->MsgSocket = $Socket;
			socket_set_nonblock($this->MsgSocket);
		}		
	}