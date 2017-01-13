<?php
require_once "connection.php";


class tiler {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $id;
	var $bf;
	var $target;
	var $status;
	var $lock;
	var $tiler_ver;
	var $ver;
	var $host;
	var $screen_id;
	var $pid;
	var $dt;


	//get bf field 
	function get_bf(){
		return $this->bf;
	}


	// set bf field
	function set_bf($value){
		$this->bf=$value;
		$sql = "UPDATE tiler set `bf`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['bf'] = "`bf` = '".addslashes($value)."'";
	}
	//get target field 
	function get_target(){
		return $this->target;
	}


	// set target field
	function set_target($value){
		$this->target=$value;
		$sql = "UPDATE tiler set `target`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['target'] = "`target` = '".addslashes($value)."'";
	}
	//get status field 
	function get_status(){
		return $this->status;
	}


	// set status field
	function set_status($value){
		$this->status=$value;
		$sql = "UPDATE tiler set `status`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['status'] = "`status` = '".addslashes($value)."'";
	}
	//get lock field 
	function get_lock(){
		return $this->lock;
	}


	// set lock field
	function set_lock($value){
		$this->lock=$value;
		$sql = "UPDATE tiler set `lock`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['lock'] = "`lock` = '".addslashes($value)."'";
	}
	//get tiler_ver field 
	function get_tiler_ver(){
		return $this->tiler_ver;
	}


	// set tiler_ver field
	function set_tiler_ver($value){
		$this->tiler_ver=$value;
		$sql = "UPDATE tiler set `tiler_ver`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['tiler_ver'] = "`tiler_ver` = '".addslashes($value)."'";
	}
	//get ver field 
	function get_ver(){
		return $this->ver;
	}


	// set ver field
	function set_ver($value){
		$this->ver=$value;
		$sql = "UPDATE tiler set `ver`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['ver'] = "`ver` = '".addslashes($value)."'";
	}
	//get host field 
	function get_host(){
		return $this->host;
	}


	// set host field
	function set_host($value){
		$this->host=$value;
		$sql = "UPDATE tiler set `host`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['host'] = "`host` = '".addslashes($value)."'";
	}
	//get screen_id field 
	function get_screen_id(){
		return $this->screen_id;
	}


	// set screen_id field
	function set_screen_id($value){
		$this->screen_id=$value;
		$sql = "UPDATE tiler set `screen_id`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['screen_id'] = "`screen_id` = '".addslashes($value)."'";
	}
	//get pid field 
	function get_pid(){
		return $this->pid;
	}


	// set pid field
	function set_pid($value){
		$this->pid=$value;
		$sql = "UPDATE tiler set `pid`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['pid'] = "`pid` = '".addslashes($value)."'";
	}
	//get dt field 
	function get_dt(){
		return $this->dt;
	}


	// set dt field
	function set_dt($value){
		$this->dt=$value;
		$sql = "UPDATE tiler set `dt`='".addslashes($value)."' WHERE id='".addslashes($this->id)."'";
		if($this->autocommit){
			if (!$this->_new) $this->connection->send_query($sql);
		}else 
			$this->UPDATE['dt'] = "`dt` = '".addslashes($value)."'";
	}
	function setRow($row){
		$this->id = $row['id'];
		$this->bf = $row['bf'];
		$this->target = $row['target'];
		$this->status = $row['status'];
		$this->lock = $row['lock'];
		$this->tiler_ver = $row['tiler_ver'];
		$this->ver = $row['ver'];
		$this->host = $row['host'];
		$this->screen_id = $row['screen_id'];
		$this->pid = $row['pid'];
		$this->dt = $row['dt'];
	}
	//Constructor
	function tiler($id='') {
		$this->connection = $GLOBALS['connection'];
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "SELECT * from tiler where id='$id'";
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->id = $row['id'];
				$this->bf = $row['bf'];
				$this->target = $row['target'];
				$this->status = $row['status'];
				$this->lock = $row['lock'];
				$this->tiler_ver = $row['tiler_ver'];
				$this->ver = $row['ver'];
				$this->host = $row['host'];
				$this->screen_id = $row['screen_id'];
				$this->pid = $row['pid'];
				$this->dt = $row['dt'];
				$this->_new = false;
			}
		}
		
	}
	//delete object on DB
	function delete(){
		$sql = "DELETE FROM `tiler` WHERE `id`='".addslashes($this->id)."'";

		$this->connection->send_query($sql);
	}
	//save changes and insert new object
	function save(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tiler` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tiler` values('".addslashes($this->id)."' , '".addslashes($this->bf)."' , '".addslashes($this->target)."' , '".addslashes($this->status)."' , '".addslashes($this->lock)."' , '".addslashes($this->tiler_ver)."' , '".addslashes($this->ver)."' , '".addslashes($this->host)."' , '".addslashes($this->screen_id)."' , '".addslashes($this->pid)."' , '".addslashes($this->dt)."')";
		}
		$this->connection->send_query($sql);
	}

	// Get the SQL code for saving this object.
	// The return string is '|' parse-able...
	function get_save_code(){
		if (isset($this->id) && !$this->_new) {
			$sql = "UPDATE `tiler` set ";
			$i = 0;
			foreach($this->UPDATE as $u){
				if ($i>0) $sql = $sql." , ";
				$sql = $sql.$u;
				$i++;
			}
			$sql = $sql." WHERE `id`='".addslashes($this->id)."'";
		}else{
			$sql = "INSERT into `tiler` values |('".addslashes($this->id)."' , '".addslashes($this->bf)."' , '".addslashes($this->target)."' , '".addslashes($this->status)."' , '".addslashes($this->lock)."' , '".addslashes($this->tiler_ver)."' , '".addslashes($this->ver)."' , '".addslashes($this->host)."' , '".addslashes($this->screen_id)."' , '".addslashes($this->pid)."' , '".addslashes($this->dt)."')";
		}
		return $sql;
	}
}
?>
