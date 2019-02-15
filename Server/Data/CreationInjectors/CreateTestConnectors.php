<?php
	global $Data;
	
	$DataString = "DestX = 171 : DestY = 202";
	$wx = 1;
	$wy = 261;
	$id = $wx.$wy;
	$Insert  = "INSERT INTO"; 
	$Insert .= " blocks(id, wx, wy, data)";
	$Insert .= " values('".$id."', '".$wx."', '".$wy."', '".$DataString."')";
	
	$Data['DataBase']->DoThis($Insert);				
?>