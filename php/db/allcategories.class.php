<?php
require_once "connection.php";


class allcategories {

	var $autocommit = 	false;
	var $UPDATE = 		array();
	var $connection;
	var $_new = 			true;

	var $bf;
	var $building;
	var $fl;
	var $l_name;
	var $l_description;
	var $lat;
	var $lon;
	var $polygon;
	var $cid;
	var $sum;
	var $c_name;
	var $c_description;
	var $image_name;
	function get_lat(){
		return $this->$lat;
	}
	function get_lon(){
		return $this->$lon;
	}
	function get_c_description(){
		return $this->c_description;
	}
	function get_l_description(){
		return $this->l_description;
	}
	function setRow($row){
				$this->bf = $row['bf'];
				$this->building = $row['building'];
				$this->fl = $row['floor'];
				$this->l_name = $row['l_name'];
				$this->l_description = $row['l_description'];
				$this->lat = $row['lat'];
				$this->lon = $row['lon'];
				$this->polygon = $row['polygon'];
				$this->cid = $row['cid'];
				$this->sum = $row['sum'];
				$this->c_name = $row['c_name'];
				$this->c_description = $row['c_description'];
				$this->image_name = $row['image_name'];
	}
	//Constructor
	function categories($id='',$cid='') {
		$this->connection = $GLOBALS['connection'];
		$sql="";
		if (strcmp($id,'')==0) {
			return;
		}
		if ((isset($id) && strcmp($id,'')!=0)) {
			$sql = "select a.*,c.name c_name,c.description c_description,c.image_name from (

			select *,sum(a.count) sum from

			(SELECT f.building,f.floor,l.bf,l.name l_name,l.description l_description,l.lat,l.lon,l.polygon,l.cid,count(l.id)count FROM floors f,locations l where f.id=l.bf and f.building<>0 and l.cid=$cid and building=$id group by l.id) a

			group by a.bf

			)a,categories c where a.cid=c.id";
			
		}else{
			$sql = "select a.*,c.name c_name,c.description c_description,c.image_name from (

			select *,sum(a.count) sum from

			(SELECT f.building,f.floor,l.bf,l.name l_name,l.description l_description,l.lat,l.lon,l.polygon,l.cid,count(l.id)count FROM floors f,locations l where f.id=l.bf and f.building<>0 and l.cid=$cid group by l.id) a

			group by a.bf

			)a,categories c where a.cid=c.id";
		}
		
			$result = $this->connection->send_query($sql);
			if (!((!$result) || $result->num_rows == 0)){
				$row = $result->fetch_assoc();

				$this->bf = $row['bf'];
				$this->building = $row['building'];
				$this->fl = $row['fl'];
				$this->l_name = $row['l_name'];
				$this->l_description = $row['l_description'];
				$this->lat = $row['lat'];
				$this->lon = $row['lon'];
				$this->polygon = $row['polygon'];
				$this->cid = $row['cid'];
				$this->sum = $row['sum'];
				$this->c_name = $row['c_name'];
				$this->c_description = $row['c_description'];
				$this->image_name = $row['image_name'];
				
				$this->_new = false;
			}
	}
}
?>
