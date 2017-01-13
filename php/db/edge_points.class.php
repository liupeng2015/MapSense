<?php
require_once "connection.php";


class edge_points {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $eid;
	var $lat;
	var $lon;
	var $alt;
	var $bf;


	//get eid field 
	function get_eid(){
		return $this->eid;
	}


	// set eid field
	function set_eid($value){
		$this->eid=$value;
		$sql = "UPDATE edge_points set `eid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['eid'] = "`eid` = '".addslashes($value)."'";
	}
	//get lat field 
	function get_lat(){
		return $this->lat;
	}


	// set lat field
	function set_lat($value){
		$this->lat=$value;
		$sql = "UPDATE edge_points set `lat`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE edge_points set `lon`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE edge_points set `alt`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['alt'] = "`alt` = '".addslashes($value)."'";
	}
	//get bf field 
	function get_bf(){
		return $this->bf;
	}


	// set bf field
	function set_bf($value){
		$this->bf=$value;
		$sql = "UPDATE edge_points set `bf`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['bf'] = "`bf` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->eid = $row['eid'];
		$this->lat = $row['lat'];
		$this->lon = $row['lon'];
		$this->alt = $row['alt'];
		$this->bf = $row['bf'];
	}
	//Constructor
	function edge_points($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from edge_points where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->eid = $row['eid'];
				$this->lat = $row['lat'];
				$this->lon = $row['lon'];
				$this->alt = $row['alt'];
				$this->bf = $row['bf'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `edge_points` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `edge_points` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `edge_points` values('".addslashes($this->id)."' , '".addslashes($this->eid)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->bf)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `edge_points` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `edge_points` values |('".addslashes($this->id)."' , '".addslashes($this->eid)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->bf)."')";
		}
		return $sql;
	}
}
?>
