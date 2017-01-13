<?php
require_once "connection.php";


class tour_cat {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $name;
	var $image_name;
	var $type;


	//get name field 
	function get_name(){
		return $this->name;
	}


	// set name field
	function set_name($value){
		$this->name=$value;
		$sql = "UPDATE tour_cat set `name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['name'] = "`name` = '".addslashes($value)."'";
	}
	//get image_name field 
	function get_image_name(){
		return $this->image_name;
	}


	// set image_name field
	function set_image_name($value){
		$this->image_name=$value;
		$sql = "UPDATE tour_cat set `image_name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['image_name'] = "`image_name` = '".addslashes($value)."'";
	}
	//get type field 
	function get_type(){
		return $this->type;
	}


	// set type field
	function set_type($value){
		$this->type=$value;
		$sql = "UPDATE tour_cat set `type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['type'] = "`type` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->image_name = $row['image_name'];
		$this->type = $row['type'];
	}
	//Constructor
	function tour_cat($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from tour_cat where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->name = $row['name'];
				$this->image_name = $row['image_name'];
				$this->type = $row['type'];
				$this->_new = false;
			}
		}
		
	}

	// Construct From Unique Column: name
	function get_from_name ($name='') {
		
		$ret = new tour_cat('');
		$this->connection = $GLOBALS['connection'];
		if ( (isset($name) && strcmp($name,'')!=0) ) {
			$sql = "SELECT * from `tour_cat` WHERE `name`='".addslashes('$name')."' LIMIT 1";
			
			$result = $this->connection->send_query($sql);
			if (!((!$result) || mysql_num_rows($result) == 0)){
				$row = mysql_fetch_assoc($result);

				$ret->id = $row['id'];
				$ret->name = $row['name'];
				$ret->image_name = $row['image_name'];
				$ret->type = $row['type'];
				$ret->_new = false;
			}
		}
		return $ret;
	}

	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `tour_cat` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tour_cat` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tour_cat` values('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->image_name)."' , '".addslashes($this->type)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tour_cat` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tour_cat` values |('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->image_name)."' , '".addslashes($this->type)."')";
		}
		return $sql;
	}
}
?>
