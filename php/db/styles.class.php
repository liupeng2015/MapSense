<?php
require_once "connection.php";


class styles {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $name;
	var $stroke;
	var $stroke_width;
	var $stroke_opacity;
	var $stroke_dasharray;
	var $fill;
	var $fill_opacity;
	var $zindex;


	//get name field 
	function get_name(){
		return $this->name;
	}


	// set name field
	function set_name($value){
		$this->name=$value;
		$sql = "UPDATE styles set `name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['name'] = "`name` = '".addslashes($value)."'";
	}
	//get stroke field 
	function get_stroke(){
		return $this->stroke;
	}


	// set stroke field
	function set_stroke($value){
		$this->stroke=$value;
		$sql = "UPDATE styles set `stroke`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stroke'] = "`stroke` = '".addslashes($value)."'";
	}
	//get stroke_width field 
	function get_stroke_width(){
		return $this->stroke_width;
	}


	// set stroke_width field
	function set_stroke_width($value){
		$this->stroke_width=$value;
		$sql = "UPDATE styles set `stroke_width`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stroke_width'] = "`stroke_width` = '".addslashes($value)."'";
	}
	//get stroke_opacity field 
	function get_stroke_opacity(){
		return $this->stroke_opacity;
	}


	// set stroke_opacity field
	function set_stroke_opacity($value){
		$this->stroke_opacity=$value;
		$sql = "UPDATE styles set `stroke_opacity`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stroke_opacity'] = "`stroke_opacity` = '".addslashes($value)."'";
	}
	//get stroke_dasharray field 
	function get_stroke_dasharray(){
		return $this->stroke_dasharray;
	}


	// set stroke_dasharray field
	function set_stroke_dasharray($value){
		$this->stroke_dasharray=$value;
		$sql = "UPDATE styles set `stroke_dasharray`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stroke_dasharray'] = "`stroke_dasharray` = '".addslashes($value)."'";
	}
	//get fill field 
	function get_fill(){
		return $this->fill;
	}


	// set fill field
	function set_fill($value){
		$this->fill=$value;
		$sql = "UPDATE styles set `fill`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['fill'] = "`fill` = '".addslashes($value)."'";
	}
	//get fill_opacity field 
	function get_fill_opacity(){
		return $this->fill_opacity;
	}


	// set fill_opacity field
	function set_fill_opacity($value){
		$this->fill_opacity=$value;
		$sql = "UPDATE styles set `fill_opacity`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['fill_opacity'] = "`fill_opacity` = '".addslashes($value)."'";
	}
	//get zindex field 
	function get_zindex(){
		return $this->zindex;
	}


	// set zindex field
	function set_zindex($value){
		$this->zindex=$value;
		$sql = "UPDATE styles set `zindex`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['zindex'] = "`zindex` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->name = $row['name'];
		$this->stroke = $row['stroke'];
		$this->stroke_width = $row['stroke_width'];
		$this->stroke_opacity = $row['stroke_opacity'];
		$this->stroke_dasharray = $row['stroke_dasharray'];
		$this->fill = $row['fill'];
		$this->fill_opacity = $row['fill_opacity'];
		$this->zindex = $row['zindex'];
	}
	//Constructor
	function styles($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from styles where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->name = $row['name'];
				$this->stroke = $row['stroke'];
				$this->stroke_width = $row['stroke_width'];
				$this->stroke_opacity = $row['stroke_opacity'];
				$this->stroke_dasharray = $row['stroke_dasharray'];
				$this->fill = $row['fill'];
				$this->fill_opacity = $row['fill_opacity'];
				$this->zindex = $row['zindex'];
				$this->_new = false;
			}
		}
		
	}

	// Construct From Unique Column: name
	function get_from_name ($name='') {
		
		$ret = new styles('');
		$this->connection = $GLOBALS['connection'];
		if ( (isset($name) && strcmp($name,'')!=0) ) {
			$sql = "SELECT * from `styles` WHERE `name`='".addslashes('$name')."' LIMIT 1";
			
			$result = $this->connection->send_query($sql);
			if (!((!$result) || mysql_num_rows($result) == 0)){
				$row = mysql_fetch_assoc($result);

				$ret->id = $row['id'];
				$ret->name = $row['name'];
				$ret->stroke = $row['stroke'];
				$ret->stroke_width = $row['stroke_width'];
				$ret->stroke_opacity = $row['stroke_opacity'];
				$ret->stroke_dasharray = $row['stroke_dasharray'];
				$ret->fill = $row['fill'];
				$ret->fill_opacity = $row['fill_opacity'];
				$ret->zindex = $row['zindex'];
				$ret->_new = false;
			}
		}
		return $ret;
	}

	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `styles` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `styles` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `styles` values('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->stroke)."' , '".addslashes($this->stroke_width)."' , '".addslashes($this->stroke_opacity)."' , '".addslashes($this->stroke_dasharray)."' , '".addslashes($this->fill)."' , '".addslashes($this->fill_opacity)."' , '".addslashes($this->zindex)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `styles` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `styles` values |('".addslashes($this->id)."' , '".addslashes($this->name)."' , '".addslashes($this->stroke)."' , '".addslashes($this->stroke_width)."' , '".addslashes($this->stroke_opacity)."' , '".addslashes($this->stroke_dasharray)."' , '".addslashes($this->fill)."' , '".addslashes($this->fill_opacity)."' , '".addslashes($this->zindex)."')";
		}
		return $sql;
	}
}
?>
