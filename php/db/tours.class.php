<?php
require_once "connection.php";


class tours {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $name;
	var $duration;
	var $description;
	var $tcid;


	//get name field 
	function get_name(){
		return $this->name;
	}


	// set name field
	function set_name($value){
		$this->name=$value;
		$sql = "UPDATE tours set `name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['name'] = "`name` = '".addslashes($value)."'";
	}
	//get duration field 
	function get_duration(){
		return $this->duration;
	}


	// set duration field
	function set_duration($value){
		$this->duration=$value;
		$sql = "UPDATE tours set `duration`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['duration'] = "`duration` = '".addslashes($value)."'";
	}
	//get description field 
	function get_description(){
		return $this->description;
	}


	// set description field
	function set_description($value){
		$this->description=$value;
		$sql = "UPDATE tours set `description`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['description'] = "`description` = '".addslashes($value)."'";
	}
	//get tcid field 
	function get_tcid(){
		return $this->tcid;
	}


	// set tcid field
	function set_tcid($value){
		$this->tcid=$value;
		$sql = "UPDATE tours set `tcid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['tcid'] = "`tcid` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->duration = $row['duration'];
		$this->description = $row['description'];
		$this->tcid = $row['tcid'];
	}
	//Constructor
	function tours($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from tours where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->name = $row['name'];
				$this->duration = $row['duration'];
				$this->description = $row['description'];
				$this->tcid = $row['tcid'];
				$this->_new = false;
			}
		}
		
	}

	// Construct From Unique Column: name
	function get_from_name ($name='') {
		
		$ret = new tours('');
		$this->connection = $GLOBALS['connection'];
		if ( (isset($name) && strcmp($name,'')!=0) ) {
			$sql = "SELECT * from `tours` WHERE `name`='".addslashes('$name')."' LIMIT 1";
			
			$result = $this->connection->send_query($sql);
			if (!((!$result) || mysql_num_rows($result) == 0)){
				$row = mysql_fetch_assoc($result);

				$ret->id = $row['id'];
				$ret->name = $row['name'];
				$ret->duration = $row['duration'];
				$ret->description = $row['description'];
				$ret->tcid = $row['tcid'];
				$ret->_new = false;
			}
		}
		return $ret;
	}

	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `tours` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tours` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tours` values('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->duration)."' , '".addslashes($this->description)."' , '".addslashes($this->tcid)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tours` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tours` values |('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->duration)."' , '".addslashes($this->description)."' , '".addslashes($this->tcid)."')";
		}
		return $sql;
	}
}
?>
