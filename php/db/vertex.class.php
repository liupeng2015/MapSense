<?php
require_once "connection.php";


class vertex {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $lat;
	var $lon;
	var $alt;
	var $assoc_type;
	var $assoc_id;
	var $link;
	var $bf;
	var $status;
	var $status_msg;
	var $path_info;


	//get lat field 
	function get_lat(){
		return $this->lat;
	}


	// set lat field
	function set_lat($value){
		$this->lat=$value;
		$sql = "UPDATE vertex set `lat`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lat'] = "`lat` = '".addslashes($value)."'";
	}
	//get lon field 
	function get_lon(){
		return $this->lon;
	}


	// set lon field
	function set_lon($value){
		$this->lon=$value;
		$sql = "UPDATE vertex set `lon`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lon'] = "`lon` = '".addslashes($value)."'";
	}
	//get alt field 
	function get_alt(){
		return $this->alt;
	}


	// set alt field
	function set_alt($value){
		$this->alt=$value;
		$sql = "UPDATE vertex set `alt`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['alt'] = "`alt` = '".addslashes($value)."'";
	}
	//get assoc_type field 
	function get_assoc_type(){
		return $this->assoc_type;
	}


	// set assoc_type field
	function set_assoc_type($value){
		$this->assoc_type=$value;
		$sql = "UPDATE vertex set `assoc_type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['assoc_type'] = "`assoc_type` = '".addslashes($value)."'";
	}
	//get assoc_id field 
	function get_assoc_id(){
		return $this->assoc_id;
	}


	// set assoc_id field
	function set_assoc_id($value){
		$this->assoc_id=$value;
		$sql = "UPDATE vertex set `assoc_id`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['assoc_id'] = "`assoc_id` = '".addslashes($value)."'";
	}
	//get link field 
	function get_link(){
		return $this->link;
	}


	// set link field
	function set_link($value){
		$this->link=$value;
		$sql = "UPDATE vertex set `link`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['link'] = "`link` = '".addslashes($value)."'";
	}
	//get bf field 
	function get_bf(){
		return $this->bf;
	}


	// set bf field
	function set_bf($value){
		$this->bf=$value;
		$sql = "UPDATE vertex set `bf`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['bf'] = "`bf` = '".addslashes($value)."'";
	}
	//get status field 
	function get_status(){
		return $this->status;
	}


	// set status field
	function set_status($value){
		$this->status=$value;
		$sql = "UPDATE vertex set `status`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE vertex set `status_msg`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['status_msg'] = "`status_msg` = '".addslashes($value)."'";
	}
	//get path_info field 
	function get_path_info(){
		return $this->path_info;
	}


	// set path_info field
	function set_path_info($value){
		$this->path_info=$value;
		$sql = "UPDATE vertex set `path_info`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['path_info'] = "`path_info` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->lat = $row['lat'];
		$this->lon = $row['lon'];
		$this->alt = $row['alt'];
		$this->assoc_type = $row['assoc_type'];
		$this->assoc_id = $row['assoc_id'];
		$this->link = $row['link'];
		$this->bf = $row['bf'];
		$this->status = $row['status'];
		$this->status_msg = $row['status_msg'];
		$this->path_info = $row['path_info'];
	}
	//Constructor
	function vertex($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from vertex where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->lat = $row['lat'];
				$this->lon = $row['lon'];
				$this->alt = $row['alt'];
				$this->assoc_type = $row['assoc_type'];
				$this->assoc_id = $row['assoc_id'];
				$this->link = $row['link'];
				$this->bf = $row['bf'];
				$this->status = $row['status'];
				$this->status_msg = $row['status_msg'];
				$this->path_info = $row['path_info'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `vertex` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `vertex` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `vertex` values('".addslashes($this->id)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->assoc_type)."' , '".addslashes($this->assoc_id)."' , '".addslashes($this->link)."' , '".addslashes($this->bf)."' , '".addslashes($this->status)."' , '".addslashes($this->status_msg)."' , '".addslashes($this->path_info)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `vertex` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `vertex` values |('".addslashes($this->id)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->assoc_type)."' , '".addslashes($this->assoc_id)."' , '".addslashes($this->link)."' , '".addslashes($this->bf)."' , '".addslashes($this->status)."' , '".addslashes($this->status_msg)."' , '".addslashes($this->path_info)."')";
		}
		return $sql;
	}
}
?>
