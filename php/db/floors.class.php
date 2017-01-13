<?php
require_once "connection.php";


class floors {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $room;
	var $floor;
	var $building;
	var $tiles;
	var $width;
	var $height;
	var $mode;
	var $maxzoom;
	var $minzoom;
	var $description;
	var $type;
	var $tile_type;
	var $ppm;
	var $oid;
	var $orig_lat_r;
	var $orig_lon_r;
	var $orig_x;
	var $orig_y;
	var $rotation_r;
	var $ppd_x;
	var $ppd_y;
	var $lat_scale_factor;
	var $lat_offset_factor;
	var $lon_scale_factor;
	var $lon_offset_factor;
	var $upm;


	//get room field 
	function get_room(){
		return $this->room;
	}


	// set room field
	function set_room($value){
		$this->room=$value;
		$sql = "UPDATE floors set `room`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['room'] = "`room` = '".addslashes($value)."'";
	}
	//get floor field 
	function get_floor(){
		return $this->floor;
	}


	// set floor field
	function set_floor($value){
		$this->floor=$value;
		$sql = "UPDATE floors set `floor`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['floor'] = "`floor` = '".addslashes($value)."'";
	}
	//get building field 
	function get_building(){
		return $this->building;
	}


	// set building field
	function set_building($value){
		$this->building=$value;
		$sql = "UPDATE floors set `building`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['building'] = "`building` = '".addslashes($value)."'";
	}
	//get tiles field 
	function get_tiles(){
		return $this->tiles;
	}


	// set tiles field
	function set_tiles($value){
		$this->tiles=$value;
		$sql = "UPDATE floors set `tiles`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['tiles'] = "`tiles` = '".addslashes($value)."'";
	}
	//get width field 
	function get_width(){
		return $this->width;
	}


	// set width field
	function set_width($value){
		$this->width=$value;
		$sql = "UPDATE floors set `width`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['width'] = "`width` = '".addslashes($value)."'";
	}
	//get height field 
	function get_height(){
		return $this->height;
	}


	// set height field
	function set_height($value){
		$this->height=$value;
		$sql = "UPDATE floors set `height`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['height'] = "`height` = '".addslashes($value)."'";
	}
	//get mode field 
	function get_mode(){
		return $this->mode;
	}


	// set mode field
	function set_mode($value){
		$this->mode=$value;
		$sql = "UPDATE floors set `mode`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['mode'] = "`mode` = '".addslashes($value)."'";
	}
	//get maxzoom field 
	function get_maxzoom(){
		return $this->maxzoom;
	}


	// set maxzoom field
	function set_maxzoom($value){
		$this->maxzoom=$value;
		$sql = "UPDATE floors set `maxzoom`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['maxzoom'] = "`maxzoom` = '".addslashes($value)."'";
	}
	//get minzoom field 
	function get_minzoom(){
		return $this->minzoom;
	}


	// set minzoom field
	function set_minzoom($value){
		$this->minzoom=$value;
		$sql = "UPDATE floors set `minzoom`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['minzoom'] = "`minzoom` = '".addslashes($value)."'";
	}
	//get description field 
	function get_description(){
		return $this->description;
	}


	// set description field
	function set_description($value){
		$this->description=$value;
		$sql = "UPDATE floors set `description`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
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
		$sql = "UPDATE floors set `type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['type'] = "`type` = '".addslashes($value)."'";
	}
	//get tile_type field 
	function get_tile_type(){
		return $this->tile_type;
	}


	// set tile_type field
	function set_tile_type($value){
		$this->tile_type=$value;
		$sql = "UPDATE floors set `tile_type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['tile_type'] = "`tile_type` = '".addslashes($value)."'";
	}
	//get ppm field 
	function get_ppm(){
		return $this->ppm;
	}


	// set ppm field
	function set_ppm($value){
		$this->ppm=$value;
		$sql = "UPDATE floors set `ppm`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['ppm'] = "`ppm` = '".addslashes($value)."'";
	}
	//get oid field 
	function get_oid(){
		return $this->oid;
	}


	// set oid field
	function set_oid($value){
		$this->oid=$value;
		$sql = "UPDATE floors set `oid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['oid'] = "`oid` = '".addslashes($value)."'";
	}
	//get orig_lat_r field 
	function get_orig_lat_r(){
		return $this->orig_lat_r;
	}


	// set orig_lat_r field
	function set_orig_lat_r($value){
		$this->orig_lat_r=$value;
		$sql = "UPDATE floors set `orig_lat_r`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['orig_lat_r'] = "`orig_lat_r` = '".addslashes($value)."'";
	}
	//get orig_lon_r field 
	function get_orig_lon_r(){
		return $this->orig_lon_r;
	}


	// set orig_lon_r field
	function set_orig_lon_r($value){
		$this->orig_lon_r=$value;
		$sql = "UPDATE floors set `orig_lon_r`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['orig_lon_r'] = "`orig_lon_r` = '".addslashes($value)."'";
	}
	//get orig_x field 
	function get_orig_x(){
		return $this->orig_x;
	}


	// set orig_x field
	function set_orig_x($value){
		$this->orig_x=$value;
		$sql = "UPDATE floors set `orig_x`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['orig_x'] = "`orig_x` = '".addslashes($value)."'";
	}
	//get orig_y field 
	function get_orig_y(){
		return $this->orig_y;
	}


	// set orig_y field
	function set_orig_y($value){
		$this->orig_y=$value;
		$sql = "UPDATE floors set `orig_y`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['orig_y'] = "`orig_y` = '".addslashes($value)."'";
	}
	//get rotation_r field 
	function get_rotation_r(){
		return $this->rotation_r;
	}


	// set rotation_r field
	function set_rotation_r($value){
		$this->rotation_r=$value;
		$sql = "UPDATE floors set `rotation_r`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['rotation_r'] = "`rotation_r` = '".addslashes($value)."'";
	}
	//get ppd_x field 
	function get_ppd_x(){
		return $this->ppd_x;
	}


	// set ppd_x field
	function set_ppd_x($value){
		$this->ppd_x=$value;
		$sql = "UPDATE floors set `ppd_x`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['ppd_x'] = "`ppd_x` = '".addslashes($value)."'";
	}
	//get ppd_y field 
	function get_ppd_y(){
		return $this->ppd_y;
	}


	// set ppd_y field
	function set_ppd_y($value){
		$this->ppd_y=$value;
		$sql = "UPDATE floors set `ppd_y`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['ppd_y'] = "`ppd_y` = '".addslashes($value)."'";
	}
	//get lat_scale_factor field 
	function get_lat_scale_factor(){
		return $this->lat_scale_factor;
	}


	// set lat_scale_factor field
	function set_lat_scale_factor($value){
		$this->lat_scale_factor=$value;
		$sql = "UPDATE floors set `lat_scale_factor`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lat_scale_factor'] = "`lat_scale_factor` = '".addslashes($value)."'";
	}
	//get lat_offset_factor field 
	function get_lat_offset_factor(){
		return $this->lat_offset_factor;
	}


	// set lat_offset_factor field
	function set_lat_offset_factor($value){
		$this->lat_offset_factor=$value;
		$sql = "UPDATE floors set `lat_offset_factor`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lat_offset_factor'] = "`lat_offset_factor` = '".addslashes($value)."'";
	}
	//get lon_scale_factor field 
	function get_lon_scale_factor(){
		return $this->lon_scale_factor;
	}


	// set lon_scale_factor field
	function set_lon_scale_factor($value){
		$this->lon_scale_factor=$value;
		$sql = "UPDATE floors set `lon_scale_factor`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lon_scale_factor'] = "`lon_scale_factor` = '".addslashes($value)."'";
	}
	//get lon_offset_factor field 
	function get_lon_offset_factor(){
		return $this->lon_offset_factor;
	}


	// set lon_offset_factor field
	function set_lon_offset_factor($value){
		$this->lon_offset_factor=$value;
		$sql = "UPDATE floors set `lon_offset_factor`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lon_offset_factor'] = "`lon_offset_factor` = '".addslashes($value)."'";
	}
	//get upm field 
	function get_upm(){
		return $this->upm;
	}


	// set upm field
	function set_upm($value){
		$this->upm=$value;
		$sql = "UPDATE floors set `upm`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['upm'] = "`upm` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->room = $row['room'];
		$this->floor = $row['floor'];
		$this->building = $row['building'];
		$this->tiles = $row['tiles'];
		$this->width = $row['width'];
		$this->height = $row['height'];
		$this->mode = $row['mode'];
		$this->maxzoom = $row['maxzoom'];
		$this->minzoom = $row['minzoom'];
		$this->description = $row['description'];
		$this->type = $row['type'];
		$this->tile_type = $row['tile_type'];
		$this->ppm = $row['ppm'];
		$this->oid = $row['oid'];
		$this->orig_lat_r = $row['orig_lat_r'];
		$this->orig_lon_r = $row['orig_lon_r'];
		$this->orig_x = $row['orig_x'];
		$this->orig_y = $row['orig_y'];
		$this->rotation_r = $row['rotation_r'];
		$this->ppd_x = $row['ppd_x'];
		$this->ppd_y = $row['ppd_y'];
		$this->lat_scale_factor = $row['lat_scale_factor'];
		$this->lat_offset_factor = $row['lat_offset_factor'];
		$this->lon_scale_factor = $row['lon_scale_factor'];
		$this->lon_offset_factor = $row['lon_offset_factor'];
		$this->upm = $row['upm'];
	}
	//Constructor
	function floors($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from floors where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->room = $row['room'];
				$this->floor = $row['floor'];
				$this->building = $row['building'];
				$this->tiles = $row['tiles'];
				$this->width = $row['width'];
				$this->height = $row['height'];
				$this->mode = $row['mode'];
				$this->maxzoom = $row['maxzoom'];
				$this->minzoom = $row['minzoom'];
				$this->description = $row['description'];
				$this->type = $row['type'];
				$this->tile_type = $row['tile_type'];
				$this->ppm = $row['ppm'];
				$this->oid = $row['oid'];
				$this->orig_lat_r = $row['orig_lat_r'];
				$this->orig_lon_r = $row['orig_lon_r'];
				$this->orig_x = $row['orig_x'];
				$this->orig_y = $row['orig_y'];
				$this->rotation_r = $row['rotation_r'];
				$this->ppd_x = $row['ppd_x'];
				$this->ppd_y = $row['ppd_y'];
				$this->lat_scale_factor = $row['lat_scale_factor'];
				$this->lat_offset_factor = $row['lat_offset_factor'];
				$this->lon_scale_factor = $row['lon_scale_factor'];
				$this->lon_offset_factor = $row['lon_offset_factor'];
				$this->upm = $row['upm'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `floors` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `floors` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `floors` values('".addslashes($this->id)."' , '".addslashes($this->room)."' , '".addslashes($this->floor)."' , '".addslashes($this->building)."' , '".addslashes($this->tiles)."' , '".addslashes($this->width)."' , '".addslashes($this->height)."' , '".addslashes($this->mode)."' , '".addslashes($this->maxzoom)."' , '".addslashes($this->minzoom)."' , '".addslashes($this->description)."' , '".addslashes($this->type)."' , '".addslashes($this->tile_type)."' , '".addslashes($this->ppm)."' , '".addslashes($this->oid)."' , '".addslashes($this->orig_lat_r)."' , '".addslashes($this->orig_lon_r)."' , '".addslashes($this->orig_x)."' , '".addslashes($this->orig_y)."' , '".addslashes($this->rotation_r)."' , '".addslashes($this->ppd_x)."' , '".addslashes($this->ppd_y)."' , '".addslashes($this->lat_scale_factor)."' , '".addslashes($this->lat_offset_factor)."' , '".addslashes($this->lon_scale_factor)."' , '".addslashes($this->lon_offset_factor)."' , '".addslashes($this->upm)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `floors` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `floors` values |('".addslashes($this->id)."' , '".addslashes($this->room)."' , '".addslashes($this->floor)."' , '".addslashes($this->building)."' , '".addslashes($this->tiles)."' , '".addslashes($this->width)."' , '".addslashes($this->height)."' , '".addslashes($this->mode)."' , '".addslashes($this->maxzoom)."' , '".addslashes($this->minzoom)."' , '".addslashes($this->description)."' , '".addslashes($this->type)."' , '".addslashes($this->tile_type)."' , '".addslashes($this->ppm)."' , '".addslashes($this->oid)."' , '".addslashes($this->orig_lat_r)."' , '".addslashes($this->orig_lon_r)."' , '".addslashes($this->orig_x)."' , '".addslashes($this->orig_y)."' , '".addslashes($this->rotation_r)."' , '".addslashes($this->ppd_x)."' , '".addslashes($this->ppd_y)."' , '".addslashes($this->lat_scale_factor)."' , '".addslashes($this->lat_offset_factor)."' , '".addslashes($this->lon_scale_factor)."' , '".addslashes($this->lon_offset_factor)."' , '".addslashes($this->upm)."')";
		}
		return $sql;
	}
}
?>
