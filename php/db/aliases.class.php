<?php
require_once "connection.php";


class aliases {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $lid;
	var $name;
	var $qr_id;


	//get lid field 
	function get_lid(){
		return $this->lid;
	}


	// set lid field
	function set_lid($value){
		$this->lid=$value;
		$sql = "UPDATE aliases set `lid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lid'] = "`lid` = '".addslashes($value)."'";
	}
	//get name field 
	function get_name(){
		return $this->name;
	}


	// set name field
	function set_name($value){
		$this->name=$value;
		$sql = "UPDATE aliases set `name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['name'] = "`name` = '".addslashes($value)."'";
	}
	//get qr_id field 
	function get_qr_id(){
		return $this->qr_id;
	}


	// set qr_id field
	function set_qr_id($value){
		$this->qr_id=$value;
		$sql = "UPDATE aliases set `qr_id`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['qr_id'] = "`qr_id` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->lid = $row['lid'];
		$this->name = $row['name'];
		$this->qr_id = $row['qr_id'];
	}
	//Constructor
	function aliases($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from aliases where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->lid = $row['lid'];
				$this->name = $row['name'];
				$this->qr_id = $row['qr_id'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `aliases` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `aliases` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `aliases` values('".addslashes($this->id)."' , '".addslashes($this->lid)."' , '".addslashes($this->name)."' , '".addslashes($this->qr_id)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `aliases` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `aliases` values |('".addslashes($this->id)."' , '".addslashes($this->lid)."' , '".addslashes($this->name)."' , '".addslashes($this->qr_id)."')";
		}
		return $sql;
	}
}
?>
