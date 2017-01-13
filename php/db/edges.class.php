<?php
require_once "connection.php";


class edges {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $start;
	var $end;
	var $inclination;
	var $stairs;
	var $length;
	var $type;
	var $status;
	var $status_msg;


	//get start field 
	function get_start(){
		return $this->start;
	}


	// set start field
	function set_start($value){
		$this->start=$value;
		$sql = "UPDATE edges set `start`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['start'] = "`start` = '".addslashes($value)."'";
	}
	//get end field 
	function get_end(){
		return $this->end;
	}


	// set end field
	function set_end($value){
		$this->end=$value;
		$sql = "UPDATE edges set `end`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['end'] = "`end` = '".addslashes($value)."'";
	}
	//get inclination field 
	function get_inclination(){
		return $this->inclination;
	}


	// set inclination field
	function set_inclination($value){
		$this->inclination=$value;
		$sql = "UPDATE edges set `inclination`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['inclination'] = "`inclination` = '".addslashes($value)."'";
	}
	//get stairs field 
	function get_stairs(){
		return $this->stairs;
	}


	// set stairs field
	function set_stairs($value){
		$this->stairs=$value;
		$sql = "UPDATE edges set `stairs`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stairs'] = "`stairs` = '".addslashes($value)."'";
	}
	//get length field 
	function get_length(){
		return $this->length;
	}


	// set length field
	function set_length($value){
		$this->length=$value;
		$sql = "UPDATE edges set `length`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['length'] = "`length` = '".addslashes($value)."'";
	}
	//get type field 
	function get_type(){
		return $this->type;
	}


	// set type field
	function set_type($value){
		$this->type=$value;
		$sql = "UPDATE edges set `type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['type'] = "`type` = '".addslashes($value)."'";
	}
	//get status field 
	function get_status(){
		return $this->status;
	}


	// set status field
	function set_status($value){
		$this->status=$value;
		$sql = "UPDATE edges set `status`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['status'] = "`status` = '".addslashes($value)."'";
	}
	//get status_msg field 
	function get_status_msg(){
		return $this->status_msg;
	}


	// set status_msg field
	function set_status_msg($value){
		$this->status_msg=$value;
		$sql = "UPDATE edges set `status_msg`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['status_msg'] = "`status_msg` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->start = $row['start'];
		$this->end = $row['end'];
		$this->inclination = $row['inclination'];
		$this->stairs = $row['stairs'];
		$this->length = $row['length'];
		$this->type = $row['type'];
		$this->status = $row['status'];
		$this->status_msg = $row['status_msg'];
	}
	//Constructor
	function edges($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from edges where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->start = $row['start'];
				$this->end = $row['end'];
				$this->inclination = $row['inclination'];
				$this->stairs = $row['stairs'];
				$this->length = $row['length'];
				$this->type = $row['type'];
				$this->status = $row['status'];
				$this->status_msg = $row['status_msg'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `edges` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `edges` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `edges` values('".addslashes($this->id)."' , '".addslashes($this->start)."' , '".addslashes($this->end)."' , '".addslashes($this->inclination)."' , '".addslashes($this->stairs)."' , '".addslashes($this->length)."' , '".addslashes($this->type)."' , '".addslashes($this->status)."' , '".addslashes($this->status_msg)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `edges` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `edges` values |('".addslashes($this->id)."' , '".addslashes($this->start)."' , '".addslashes($this->end)."' , '".addslashes($this->inclination)."' , '".addslashes($this->stairs)."' , '".addslashes($this->length)."' , '".addslashes($this->type)."' , '".addslashes($this->status)."' , '".addslashes($this->status_msg)."')";
		}
		return $sql;
	}
}
?>
