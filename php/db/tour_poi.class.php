<?php
require_once "connection.php";


class tour_poi {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $tid;
	var $lid;
	var $order;


	//get tid field 
	function get_tid(){
		return $this->tid;
	}


	// set tid field
	function set_tid($value){
		$this->tid=$value;
		$sql = "UPDATE tour_poi set `tid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['tid'] = "`tid` = '".addslashes($value)."'";
	}
	//get lid field 
	function get_lid(){
		return $this->lid;
	}


	// set lid field
	function set_lid($value){
		$this->lid=$value;
		$sql = "UPDATE tour_poi set `lid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lid'] = "`lid` = '".addslashes($value)."'";
	}
	//get order field 
	function get_order(){
		return $this->order;
	}


	// set order field
	function set_order($value){
		$this->order=$value;
		$sql = "UPDATE tour_poi set `order`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['order'] = "`order` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->tid = $row['tid'];
		$this->lid = $row['lid'];
		$this->order = $row['order'];
	}
	//Constructor
	function tour_poi($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from tour_poi where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->tid = $row['tid'];
				$this->lid = $row['lid'];
				$this->order = $row['order'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `tour_poi` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tour_poi` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tour_poi` values('".addslashes($this->id)."' , '".addslashes($this->tid)."' , '".addslashes($this->lid)."' , '".addslashes($this->order)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tour_poi` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tour_poi` values |('".addslashes($this->id)."' , '".addslashes($this->tid)."' , '".addslashes($this->lid)."' , '".addslashes($this->order)."')";
		}
		return $sql;
	}
}
?>
