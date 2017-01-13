<?php
require_once "connection.php";


class layer_points {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $lid;
	var $name;
	var $lat;
	var $lon;
	var $alt;
	var $bf;
	var $lp_image;
	var $notes;


	//get lid field 
	function get_lid(){
		return $this->lid;
	}


	// set lid field
	function set_lid($value){
		$this->lid=$value;
		$sql = "UPDATE layer_points set `lid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE layer_points set `name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['name'] = "`name` = '".addslashes($value)."'";
	}
	//get lat field 
	function get_lat(){
		return $this->lat;
	}


	// set lat field
	function set_lat($value){
		$this->lat=$value;
		$sql = "UPDATE layer_points set `lat`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE layer_points set `lon`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE layer_points set `alt`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE layer_points set `bf`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['bf'] = "`bf` = '".addslashes($value)."'";
	}
	//get lp_image field 
	function get_lp_image(){
		return $this->lp_image;
	}


	// set lp_image field
	function set_lp_image($value){
		$this->lp_image=$value;
		$sql = "UPDATE layer_points set `lp_image`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lp_image'] = "`lp_image` = '".addslashes($value)."'";
	}
	//get notes field 
	function get_notes(){
		return $this->notes;
	}


	// set notes field
	function set_notes($value){
		$this->notes=$value;
		$sql = "UPDATE layer_points set `notes`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['notes'] = "`notes` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->lid = $row['lid'];
		$this->name = $row['name'];
		$this->lat = $row['lat'];
		$this->lon = $row['lon'];
		$this->alt = $row['alt'];
		$this->bf = $row['bf'];
		$this->lp_image = $row['lp_image'];
		$this->notes = $row['notes'];
	}
	//Constructor
	function layer_points($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from layer_points where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->lid = $row['lid'];
				$this->name = $row['name'];
				$this->lat = $row['lat'];
				$this->lon = $row['lon'];
				$this->alt = $row['alt'];
				$this->bf = $row['bf'];
				$this->lp_image = $row['lp_image'];
				$this->notes = $row['notes'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `layer_points` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `layer_points` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `layer_points` values('".addslashes($this->id)."' , '".addslashes($this->lid)."' , '".addslashes($this->name)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->bf)."' , '".addslashes($this->lp_image)."' , '".addslashes($this->notes)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `layer_points` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `layer_points` values |('".addslashes($this->id)."' , '".addslashes($this->lid)."' , '".addslashes($this->name)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->bf)."' , '".addslashes($this->lp_image)."' , '".addslashes($this->notes)."')";
		}
		return $sql;
	}
}
?>
