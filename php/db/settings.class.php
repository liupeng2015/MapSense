<?php
require_once "connection.php";


class settings {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $yah_image;
	var $indoor_pin;
	var $initial_node_id;
	var $path_thickness;
	var $path_stroke;
	var $path_color;
	var $inout_scale_factor;
	var $default_bf;
	var $site_type;
	var $pstyle;
	var $prev_pin;
	var $next_pin;
	var $search_pin;
	var $start_pin;
	var $end_pin;
	var $broker_url;
	var $meta_to;
	var $outpstyle;
	var $outpath_color;
	var $auth_name;
	var $auth_email;
	var $dbver;
	var $font_family;
	var $tiles_out;
	var $tiles_in;


	//get yah_image field 
	function get_yah_image(){
		return $this->yah_image;
	}


	// set yah_image field
	function set_yah_image($value){
		$this->yah_image=$value;
		$sql = "UPDATE settings set `yah_image`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['yah_image'] = "`yah_image` = '".addslashes($value)."'";
	}
	//get indoor_pin field 
	function get_indoor_pin(){
		return $this->indoor_pin;
	}


	// set indoor_pin field
	function set_indoor_pin($value){
		$this->indoor_pin=$value;
		$sql = "UPDATE settings set `indoor_pin`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['indoor_pin'] = "`indoor_pin` = '".addslashes($value)."'";
	}
	//get initial_node_id field 
	function get_initial_node_id(){
		return $this->initial_node_id;
	}


	// set initial_node_id field
	function set_initial_node_id($value){
		$this->initial_node_id=$value;
		$sql = "UPDATE settings set `initial_node_id`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['initial_node_id'] = "`initial_node_id` = '".addslashes($value)."'";
	}
	//get path_thickness field 
	function get_path_thickness(){
		return $this->path_thickness;
	}


	// set path_thickness field
	function set_path_thickness($value){
		$this->path_thickness=$value;
		$sql = "UPDATE settings set `path_thickness`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['path_thickness'] = "`path_thickness` = '".addslashes($value)."'";
	}
	//get path_stroke field 
	function get_path_stroke(){
		return $this->path_stroke;
	}


	// set path_stroke field
	function set_path_stroke($value){
		$this->path_stroke=$value;
		$sql = "UPDATE settings set `path_stroke`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['path_stroke'] = "`path_stroke` = '".addslashes($value)."'";
	}
	//get path_color field 
	function get_path_color(){
		return $this->path_color;
	}


	// set path_color field
	function set_path_color($value){
		$this->path_color=$value;
		$sql = "UPDATE settings set `path_color`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['path_color'] = "`path_color` = '".addslashes($value)."'";
	}
	//get inout_scale_factor field 
	function get_inout_scale_factor(){
		return $this->inout_scale_factor;
	}


	// set inout_scale_factor field
	function set_inout_scale_factor($value){
		$this->inout_scale_factor=$value;
		$sql = "UPDATE settings set `inout_scale_factor`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['inout_scale_factor'] = "`inout_scale_factor` = '".addslashes($value)."'";
	}
	//get default_bf field 
	function get_default_bf(){
		return $this->default_bf;
	}


	// set default_bf field
	function set_default_bf($value){
		$this->default_bf=$value;
		$sql = "UPDATE settings set `default_bf`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['default_bf'] = "`default_bf` = '".addslashes($value)."'";
	}
	//get site_type field 
	function get_site_type(){
		return $this->site_type;
	}


	// set site_type field
	function set_site_type($value){
		$this->site_type=$value;
		$sql = "UPDATE settings set `site_type`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['site_type'] = "`site_type` = '".addslashes($value)."'";
	}
	//get pstyle field 
	function get_pstyle(){
		return $this->pstyle;
	}


	// set pstyle field
	function set_pstyle($value){
		$this->pstyle=$value;
		$sql = "UPDATE settings set `pstyle`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['pstyle'] = "`pstyle` = '".addslashes($value)."'";
	}
	//get prev_pin field 
	function get_prev_pin(){
		return $this->prev_pin;
	}


	// set prev_pin field
	function set_prev_pin($value){
		$this->prev_pin=$value;
		$sql = "UPDATE settings set `prev_pin`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['prev_pin'] = "`prev_pin` = '".addslashes($value)."'";
	}
	//get next_pin field 
	function get_next_pin(){
		return $this->next_pin;
	}


	// set next_pin field
	function set_next_pin($value){
		$this->next_pin=$value;
		$sql = "UPDATE settings set `next_pin`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['next_pin'] = "`next_pin` = '".addslashes($value)."'";
	}
	//get search_pin field 
	function get_search_pin(){
		return $this->search_pin;
	}


	// set search_pin field
	function set_search_pin($value){
		$this->search_pin=$value;
		$sql = "UPDATE settings set `search_pin`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['search_pin'] = "`search_pin` = '".addslashes($value)."'";
	}
	//get start_pin field 
	function get_start_pin(){
		return $this->start_pin;
	}


	// set start_pin field
	function set_start_pin($value){
		$this->start_pin=$value;
		$sql = "UPDATE settings set `start_pin`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['start_pin'] = "`start_pin` = '".addslashes($value)."'";
	}
	//get end_pin field 
	function get_end_pin(){
		return $this->end_pin;
	}


	// set end_pin field
	function set_end_pin($value){
		$this->end_pin=$value;
		$sql = "UPDATE settings set `end_pin`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['end_pin'] = "`end_pin` = '".addslashes($value)."'";
	}
	//get broker_url field 
	function get_broker_url(){
		return $this->broker_url;
	}


	// set broker_url field
	function set_broker_url($value){
		$this->broker_url=$value;
		$sql = "UPDATE settings set `broker_url`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['broker_url'] = "`broker_url` = '".addslashes($value)."'";
	}
	//get meta_to field 
	function get_meta_to(){
		return $this->meta_to;
	}


	// set meta_to field
	function set_meta_to($value){
		$this->meta_to=$value;
		$sql = "UPDATE settings set `meta_to`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['meta_to'] = "`meta_to` = '".addslashes($value)."'";
	}
	//get outpstyle field 
	function get_outpstyle(){
		return $this->outpstyle;
	}


	// set outpstyle field
	function set_outpstyle($value){
		$this->outpstyle=$value;
		$sql = "UPDATE settings set `outpstyle`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['outpstyle'] = "`outpstyle` = '".addslashes($value)."'";
	}
	//get outpath_color field 
	function get_outpath_color(){
		return $this->outpath_color;
	}


	// set outpath_color field
	function set_outpath_color($value){
		$this->outpath_color=$value;
		$sql = "UPDATE settings set `outpath_color`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['outpath_color'] = "`outpath_color` = '".addslashes($value)."'";
	}
	//get auth_name field 
	function get_auth_name(){
		return $this->auth_name;
	}


	// set auth_name field
	function set_auth_name($value){
		$this->auth_name=$value;
		$sql = "UPDATE settings set `auth_name`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['auth_name'] = "`auth_name` = '".addslashes($value)."'";
	}
	//get auth_email field 
	function get_auth_email(){
		return $this->auth_email;
	}


	// set auth_email field
	function set_auth_email($value){
		$this->auth_email=$value;
		$sql = "UPDATE settings set `auth_email`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['auth_email'] = "`auth_email` = '".addslashes($value)."'";
	}
	//get dbver field 
	function get_dbver(){
		return $this->dbver;
	}


	// set dbver field
	function set_dbver($value){
		$this->dbver=$value;
		$sql = "UPDATE settings set `dbver`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['dbver'] = "`dbver` = '".addslashes($value)."'";
	}
	//get font_family field 
	function get_font_family(){
		return $this->font_family;
	}


	// set font_family field
	function set_font_family($value){
		$this->font_family=$value;
		$sql = "UPDATE settings set `font_family`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['font_family'] = "`font_family` = '".addslashes($value)."'";
	}
	//get tiles_out field 
	function get_tiles_out(){
		return $this->tiles_out;
	}


	// set tiles_out field
	function set_tiles_out($value){
		$this->tiles_out=$value;
		$sql = "UPDATE settings set `tiles_out`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['tiles_out'] = "`tiles_out` = '".addslashes($value)."'";
	}
	//get tiles_in field 
	function get_tiles_in(){
		return $this->tiles_in;
	}


	// set tiles_in field
	function set_tiles_in($value){
		$this->tiles_in=$value;
		$sql = "UPDATE settings set `tiles_in`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['tiles_in'] = "`tiles_in` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->yah_image = $row['yah_image'];
		$this->indoor_pin = $row['indoor_pin'];
		$this->initial_node_id = $row['initial_node_id'];
		$this->path_thickness = $row['path_thickness'];
		$this->path_stroke = $row['path_stroke'];
		$this->path_color = $row['path_color'];
		$this->inout_scale_factor = $row['inout_scale_factor'];
		$this->default_bf = $row['default_bf'];
		$this->site_type = $row['site_type'];
		$this->pstyle = $row['pstyle'];
		$this->prev_pin = $row['prev_pin'];
		$this->next_pin = $row['next_pin'];
		$this->search_pin = $row['search_pin'];
		$this->start_pin = $row['start_pin'];
		$this->end_pin = $row['end_pin'];
		$this->broker_url = $row['broker_url'];
		$this->meta_to = $row['meta_to'];
		$this->outpstyle = $row['outpstyle'];
		$this->outpath_color = $row['outpath_color'];
		$this->auth_name = $row['auth_name'];
		$this->auth_email = $row['auth_email'];
		$this->dbver = $row['dbver'];
		$this->font_family = $row['font_family'];
		$this->tiles_out = $row['tiles_out'];
		$this->tiles_in = $row['tiles_in'];
	}
	//Constructor
	function settings($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from settings where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->yah_image = $row['yah_image'];
				$this->indoor_pin = $row['indoor_pin'];
				$this->initial_node_id = $row['initial_node_id'];
				$this->path_thickness = $row['path_thickness'];
				$this->path_stroke = $row['path_stroke'];
				$this->path_color = $row['path_color'];
				$this->inout_scale_factor = $row['inout_scale_factor'];
				$this->default_bf = $row['default_bf'];
				$this->site_type = $row['site_type'];
				$this->pstyle = $row['pstyle'];
				$this->prev_pin = $row['prev_pin'];
				$this->next_pin = $row['next_pin'];
				$this->search_pin = $row['search_pin'];
				$this->start_pin = $row['start_pin'];
				$this->end_pin = $row['end_pin'];
				$this->broker_url = $row['broker_url'];
				$this->meta_to = $row['meta_to'];
				$this->outpstyle = $row['outpstyle'];
				$this->outpath_color = $row['outpath_color'];
				$this->auth_name = $row['auth_name'];
				$this->auth_email = $row['auth_email'];
				$this->dbver = $row['dbver'];
				$this->font_family = $row['font_family'];
				$this->tiles_out = $row['tiles_out'];
				$this->tiles_in = $row['tiles_in'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `settings` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `settings` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `settings` values('".addslashes($this->id)."' , '".addslashes($this->yah_image)."' , '".addslashes($this->indoor_pin)."' , '".addslashes($this->initial_node_id)."' , '".addslashes($this->path_thickness)."' , '".addslashes($this->path_stroke)."' , '".addslashes($this->path_color)."' , '".addslashes($this->inout_scale_factor)."' , '".addslashes($this->default_bf)."' , '".addslashes($this->site_type)."' , '".addslashes($this->pstyle)."' , '".addslashes($this->prev_pin)."' , '".addslashes($this->next_pin)."' , '".addslashes($this->search_pin)."' , '".addslashes($this->start_pin)."' , '".addslashes($this->end_pin)."' , '".addslashes($this->broker_url)."' , '".addslashes($this->meta_to)."' , '".addslashes($this->outpstyle)."' , '".addslashes($this->outpath_color)."' , '".addslashes($this->auth_name)."' , '".addslashes($this->auth_email)."' , '".addslashes($this->dbver)."' , '".addslashes($this->font_family)."' , '".addslashes($this->tiles_out)."' , '".addslashes($this->tiles_in)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `settings` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `settings` values |('".addslashes($this->id)."' , '".addslashes($this->yah_image)."' , '".addslashes($this->indoor_pin)."' , '".addslashes($this->initial_node_id)."' , '".addslashes($this->path_thickness)."' , '".addslashes($this->path_stroke)."' , '".addslashes($this->path_color)."' , '".addslashes($this->inout_scale_factor)."' , '".addslashes($this->default_bf)."' , '".addslashes($this->site_type)."' , '".addslashes($this->pstyle)."' , '".addslashes($this->prev_pin)."' , '".addslashes($this->next_pin)."' , '".addslashes($this->search_pin)."' , '".addslashes($this->start_pin)."' , '".addslashes($this->end_pin)."' , '".addslashes($this->broker_url)."' , '".addslashes($this->meta_to)."' , '".addslashes($this->outpstyle)."' , '".addslashes($this->outpath_color)."' , '".addslashes($this->auth_name)."' , '".addslashes($this->auth_email)."' , '".addslashes($this->dbver)."' , '".addslashes($this->font_family)."' , '".addslashes($this->tiles_out)."' , '".addslashes($this->tiles_in)."')";
		}
		return $sql;
	}
}
?>
