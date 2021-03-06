<?php
require_once "connection.php";


class categories {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $pid;
	var $name;
	var $description;
	var $type;
	var $image_name;
	var $stid;


	//get pid field 
	function get_pid(){
		return $this->pid;
	}


	// set pid field
	function set_pid($value){
		$this->pid=$value;
		$sql = "UPDATE categories set `pid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['pid'] = "`pid` = '".addslashes($value)."'";
	}
	//get name field 
	function get_name(){
		return $this->name;
	}

	//get description field 
	function get_description(){
		return $this->description;
	}

	// set name field
	function set_name($value){
		$this->name=$value;
		$sql = "UPDATE categories set `name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['name'] = "`name` = '".addslashes($value)."'";
	}

	// set description field
	function set_description($value){
		$this->description=$value;
		$sql = "UPDATE categories set `description`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['description'] = "`description` = '".addslashes($value)."'";
	}

	//get type field 
	function get_type(){
		return $this->type;
	}


	// set type field
	function set_type($value){
		$this->type=$value;
		$sql = "UPDATE categories set `type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['type'] = "`type` = '".addslashes($value)."'";
	}
	//get image_name field 
	function get_image_name(){
		return $this->image_name;
	}


	// set image_name field
	function set_image_name($value){
		$this->image_name=$value;
		$sql = "UPDATE categories set `image_name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['image_name'] = "`image_name` = '".addslashes($value)."'";
	}
	//get stid field 
	function get_stid(){
		return $this->stid;
	}


	// set stid field
	function set_stid($value){
		$this->stid=$value;
		$sql = "UPDATE categories set `stid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stid'] = "`stid` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->pid = $row['pid'];
		$this->name = $row['name'];
		$this->description = $row['description'];
		$this->type = $row['type'];
		$this->image_name = $row['image_name'];
		$this->stid = $row['stid'];
	}
	//Constructor
	function categories($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from categories where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->pid = $row['pid'];
				$this->name = $row['name'];
				$this->description = $row['description'];
				$this->type = $row['type'];
				$this->image_name = $row['image_name'];
				$this->stid = $row['stid'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `categories` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `categories` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `categories` values('".addslashes($this->id)."' , '".addslashes($this->pid)."' , '".addslashes($this->name)."' , '".addslashes($this->description)."' , '".addslashes($this->type)."' , '".addslashes($this->image_name)."' , '".addslashes($this->stid)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `categories` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `categories` values |('".addslashes($this->id)."' , '".addslashes($this->pid)."' , '".addslashes($this->name)."' , '".addslashes($this->description)."' , '".addslashes($this->type)."' , '".addslashes($this->image_name)."' , '".addslashes($this->stid)."')";
		}
		return $sql;
	}
}
?>
