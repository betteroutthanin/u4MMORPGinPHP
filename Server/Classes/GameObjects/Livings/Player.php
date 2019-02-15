<?php
	class Player extends Living
	{
		protected $Items;
		protected $PendingAction;
		
		// ******************************
		function __construct()
		{
			parent::__construct();
			
			$this->Items = array();
			$this->PendingAction = NULL;
			$this->RID = 102;
		}
		
		// ******************************
		function __destruct()
		{
			$this->GOB("!!!! destruct() !!!!!");			
		}

		// ******************************
		public function Tick()
		{
			global $Data;
			
			// Hack
			// $this->NextThink = 0;
			
			parent::Tick();

			// Player should excute any pending actions
			// Pending actions are placed in a que by
			// the connection.			
			if ($this->PendingAction == NULL)
			{
				return;
			}
			
			// Only attempt the action if the next this
			// has expired
			if ($Data['ServerFrame'] < $this->NextThink)
			{
				return;
			}
			
			// Excute and clear the PendingAction
			$Data['AIF']->Act($this->PendingAction);
			$this->PendingAction = NULL;			
		}
		
		// ******************************
		public function AddItemByID($ID)
		{
			$this->Items[$ID] = $ID;
			$this->GOB("Got item ->".$ID);			
		}
		
		// ******************************
		public function RemoveItemByID($ID)
		{
			unset($this->Items[$ID]);
			$this->GOB("Removed item ->".$ID);			
		}

		// ******************************
		public function SetPendingAction($ActionArray)
		{
			$this->PendingAction = $ActionArray;
		}
	}
?>