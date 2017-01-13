<?php
require_once "connection.php";


class locations {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $name;
	var $lat;
	var $lon;
	var $alt;
	var $bf;
	var $description;
	var $entr;
	var $cid;
	var $polygon;
	var $qr_id;
	var $meta;
	var $meta_ts;
	var $client_id;
	var $stid;


	//get name field 
	function get_name(){
		return $this->name;
	}


	// set name field
	function set_name($value){
		$this->name=html_entity_decode(addslashes($value));
		$sql = "UPDATE locations set `name`='$this->name' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['name'] = "`name` = '$this->name'";
	}
	//get lat field 
	function get_lat(){
		return $this->lat;
	}


	// set lat field
	function set_lat($value){
		$this->lat=$value;
		$sql = "UPDATE locations set `lat`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE locations set `lon`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE locations set `alt`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE locations set `bf`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['bf'] = "`bf` = '".addslashes($value)."'";
	}
	//get description field 
	function get_description(){
		return $this->description;
	}


	// set description field
	function set_description($value){
		$this->description=$value;
		$sql = "UPDATE locations set `description`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['description'] = "`description` = '".addslashes($value)."'";
	}
	//get entr field 
	function get_entr(){
		return $this->entr;
	}


	// set entr field
	function set_entr($value){
		$this->entr=$value;
		$sql = "UPDATE locations set `entr`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['entr'] = "`entr` = '".addslashes($value)."'";
	}
	//get cid field 
	function get_cid(){
		return $this->cid;
	}


	// set cid field
	function set_cid($value){
		$this->cid=$value;
		$sql = "UPDATE locations set `cid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['cid'] = "`cid` = '".addslashes($value)."'";
	}
	//get polygon field 
	function get_polygon(){
		return $this->polygon;
	}


	// set polygon field
	function set_polygon($value){
		$this->polygon=$value;
		$sql = "UPDATE locations set `polygon`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['polygon'] = "`polygon` = '".addslashes($value)."'";
	}
	//get qr_id field 
	function get_qr_id(){
		return $this->qr_id;
	}


	// set qr_id field
	function set_qr_id($value){
		$this->qr_id=$value;
		$sql = "UPDATE locations set `qr_id`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['qr_id'] = "`qr_id` = '".addslashes($value)."'";
	}
	//get meta field 
	function get_meta(){
		return $this->meta;
	}


	// set meta field
	function set_meta($value){
		$this->meta=$value;
		$sql = "UPDATE locations set `meta`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['meta'] = "`meta` = '".addslashes($value)."'";
	}
	//get meta_ts field 
	function get_meta_ts(){
		return $this->meta_ts;
	}


	// set meta_ts field
	function set_meta_ts($value){
		$this->meta_ts=$value;
		$sql = "UPDATE locations set `meta_ts`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['meta_ts'] = "`meta_ts` = '".addslashes($value)."'";
	}
	//get client_id field 
	function get_client_id(){
		return $this->client_id;
	}


	// set client_id field
	function set_client_id($value){
		$this->client_id=$value;
		$sql = "UPDATE locations set `client_id`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['client_id'] = "`client_id` = '".addslashes($value)."'";
	}
	//get stid field 
	function get_stid(){
		return $this->stid;
	}


	// set stid field
	function set_stid($value){
		$this->stid=$value;
		$sql = "UPDATE locations set `stid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stid'] = "`stid` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->lat = $row['lat'];
		$this->lon = $row['lon'];
		$this->alt = $row['alt'];
		$this->bf = $row['bf'];
		$this->description = $row['description'];
		$this->entr = $row['entr'];
		$this->cid = $row['cid'];
		$this->polygon = $row['polygon'];
		$this->qr_id = $row['qr_id'];
		$this->meta = $row['meta'];
		$this->meta_ts = $row['meta_ts'];
		$this->client_id = $row['client_id'];
		$this->stid = $row['stid'];
	}
	//Constructor
	function locations($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from locations where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->name = $row['name'];
				$this->lat = $row['lat'];
				$this->lon = $row['lon'];
				$this->alt = $row['alt'];
				$this->bf = $row['bf'];
				$this->description = $row['description'];
				$this->entr = $row['entr'];
				$this->cid = $row['cid'];
				$this->polygon = $row['polygon'];
				$this->qr_id = $row['qr_id'];
				$this->meta = $row['meta'];
				$this->meta_ts = $row['meta_ts'];
				$this->client_id = $row['client_id'];
				$this->stid = $row['stid'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `locations` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `locations` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `locations` values('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->bf)."' , '".addslashes($this->description)."' , '".addslashes($this->entr)."' , '".addslashes($this->cid)."' , '".addslashes($this->polygon)."' , '".addslashes($this->qr_id)."' , '".addslashes($this->meta)."' , '".addslashes($this->meta_ts)."' , '".addslashes($this->client_id)."' , '".addslashes($this->stid)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `locations` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `locations` values |('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->lat)."' , '".addslashes($this->lon)."' , '".addslashes($this->alt)."' , '".addslashes($this->bf)."' , '".addslashes($this->description)."' , '".addslashes($this->entr)."' , '".addslashes($this->cid)."' , '".addslashes($this->polygon)."' , '".addslashes($this->qr_id)."' , '".addslashes($this->meta)."' , '".addslashes($this->meta_ts)."' , '".addslashes($this->client_id)."' , '".addslashes($this->stid)."')";
		}
		return $sql;
	}
}
?>
