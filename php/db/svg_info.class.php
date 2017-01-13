<?php
require_once "connection.php";


class svg_info {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $bf;
	var $type;
	var $lid;
	var $index;
	var $transform;
	var $value;
	var $fontsize;
	var $color;
	var $fill;
	var $stroke;
	var $points;
	var $txt;


	//get bf field 
	function get_bf(){
		return $this->bf;
	}


	// set bf field
	function set_bf($value){
		$this->bf=$value;
		$sql = "UPDATE svg_info set `bf`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['bf'] = "`bf` = '".addslashes($value)."'";
	}
	//get type field 
	function get_type(){
		return $this->type;
	}


	// set type field
	function set_type($value){
		$this->type=$value;
		$sql = "UPDATE svg_info set `type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['type'] = "`type` = '".addslashes($value)."'";
	}
	//get lid field 
	function get_lid(){
		return $this->lid;
	}


	// set lid field
	function set_lid($value){
		$this->lid=$value;
		$sql = "UPDATE svg_info set `lid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lid'] = "`lid` = '".addslashes($value)."'";
	}
	//get index field 
	function get_index(){
		return $this->index;
	}


	// set index field
	function set_index($value){
		$this->index=$value;
		$sql = "UPDATE svg_info set `index`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['index'] = "`index` = '".addslashes($value)."'";
	}
	//get transform field 
	function get_transform(){
		return $this->transform;
	}


	// set transform field
	function set_transform($value){
		$this->transform=$value;
		$sql = "UPDATE svg_info set `transform`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['transform'] = "`transform` = '".addslashes($value)."'";
	}
	//get value field 
	function get_value(){
		return $this->value;
	}


	// set value field
	function set_value($value){
		$this->value=$value;
		$sql = "UPDATE svg_info set `value`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['value'] = "`value` = '".addslashes($value)."'";
	}
	//get fontsize field 
	function get_fontsize(){
		return $this->fontsize;
	}


	// set fontsize field
	function set_fontsize($value){
		$this->fontsize=$value;
		$sql = "UPDATE svg_info set `fontsize`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['fontsize'] = "`fontsize` = '".addslashes($value)."'";
	}
	//get color field 
	function get_color(){
		return $this->color;
	}


	// set color field
	function set_color($value){
		$this->color=$value;
		$sql = "UPDATE svg_info set `color`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['color'] = "`color` = '".addslashes($value)."'";
	}
	//get fill field 
	function get_fill(){
		return $this->fill;
	}


	// set fill field
	function set_fill($value){
		$this->fill=$value;
		$sql = "UPDATE svg_info set `fill`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['fill'] = "`fill` = '".addslashes($value)."'";
	}
	//get stroke field 
	function get_stroke(){
		return $this->stroke;
	}


	// set stroke field
	function set_stroke($value){
		$this->stroke=$value;
		$sql = "UPDATE svg_info set `stroke`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['stroke'] = "`stroke` = '".addslashes($value)."'";
	}
	//get points field 
	function get_points(){
		return $this->points;
	}


	// set points field
	function set_points($value){
		$this->points=$value;
		$sql = "UPDATE svg_info set `points`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['points'] = "`points` = '".addslashes($value)."'";
	}
	//get txt field 
	function get_txt(){
		return $this->txt;
	}


	// set txt field
	function set_txt($value){
		$this->txt=$value;
		$sql = "UPDATE svg_info set `txt`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['txt'] = "`txt` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->bf = $row['bf'];
		$this->type = $row['type'];
		$this->lid = $row['lid'];
		$this->index = $row['index'];
		$this->transform = $row['transform'];
		$this->value = $row['value'];
		$this->fontsize = $row['fontsize'];
		$this->color = $row['color'];
		$this->fill = $row['fill'];
		$this->stroke = $row['stroke'];
		$this->points = $row['points'];
		$this->txt = $row['txt'];
	}
	//Constructor
	function svg_info($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from svg_info where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->bf = $row['bf'];
				$this->type = $row['type'];
				$this->lid = $row['lid'];
				$this->index = $row['index'];
				$this->transform = $row['transform'];
				$this->value = $row['value'];
				$this->fontsize = $row['fontsize'];
				$this->color = $row['color'];
				$this->fill = $row['fill'];
				$this->stroke = $row['stroke'];
				$this->points = $row['points'];
				$this->txt = $row['txt'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `svg_info` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `svg_info` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `svg_info` values('".addslashes($this->id)."' , '".addslashes($this->bf)."' , '".addslashes($this->type)."' , '".addslashes($this->lid)."' , '".addslashes($this->index)."' , '".addslashes($this->transform)."' , '".addslashes($this->value)."' , '".addslashes($this->fontsize)."' , '".addslashes($this->color)."' , '".addslashes($this->fill)."' , '".addslashes($this->stroke)."' , '".addslashes($this->points)."' , '".addslashes($this->txt)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `svg_info` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `svg_info` values |('".addslashes($this->id)."' , '".addslashes($this->bf)."' , '".addslashes($this->type)."' , '".addslashes($this->lid)."' , '".addslashes($this->index)."' , '".addslashes($this->transform)."' , '".addslashes($this->value)."' , '".addslashes($this->fontsize)."' , '".addslashes($this->color)."' , '".addslashes($this->fill)."' , '".addslashes($this->stroke)."' , '".addslashes($this->points)."' , '".addslashes($this->txt)."')";
		}
		return $sql;
	}
}
?>
