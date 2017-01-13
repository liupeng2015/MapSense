<?php

require_once "connection.php";

// $connection = new connection();
// $connection->checkLink();
// $GLOBALS['connection'] = $connection;

function getList_aliases($condition,$extra=""){
	global $connection;
	require_once("aliases.class.php");
	$sql = "SELECT * FROM `aliases`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new aliases();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_categories($condition,$extra=""){
	global $connection;
	require_once("categories.class.php");
	$sql = "SELECT * FROM `categories`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new categories();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_allcategories($id='',$cid=''){
	global $connection;
	require_once("allcategories.class.php");
		if (strcmp($id,'')==0) {
			return;
		}
		$sql="";
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
	//file_put_contents("/var/www/RL3/php.log","sql = ".$sql."\n",FILE_APPEND);
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new allcategories();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_buildinglocations(){
	global $connection;
	require_once("locations.class.php");
	$sql = "SELECT l.* FROM  locations l, floors f WHERE f.description=l.name group by l.name;";
	//"SELECT l.* FROM  locations l, floors f WHERE l.bf = f.id and f.building=0;";
			
	//file_put_contents("/var/www/RL3/php.log","sql = ".$sql."\n",FILE_APPEND);
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new locations();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_catmap($condition,$extra=""){
	global $connection;
	require_once("catmap.class.php");
	$sql = "SELECT * FROM `catmap`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new catmap();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_edge_points($condition,$extra=""){
	global $connection;
	require_once("edge_points.class.php");
	$sql = "SELECT * FROM `edge_points`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new edge_points();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_edges($condition,$extra=""){
	global $connection;
	require_once("edges.class.php");
	$sql = "SELECT * FROM `edges`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new edges();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_floors($condition,$extra=""){
	global $connection;
	require_once("floors.class.php");
	$sql = "SELECT * FROM `floors`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	//file_put_contents("/var/www/RL3/php.log","sql=$sql\n",FILE_APPEND);
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new floors();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_layer_points($condition,$extra=""){
	global $connection;
	require_once("layer_points.class.php");
	$sql = "SELECT * FROM `layer_points`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new layer_points();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_building_layer_points($id='',$cid=''){
	global $connection;
	require_once("allcategories.class.php");
	
	//file_put_contents("/var/www/RL3/php.log",$id."--".$cid."\n",FILE_APPEND);
		if (strcmp($id,'')==0) {
			return;
		}
		$sql = "select a.*,
c.name c_name,
c.image_name from (

			select *,sum(a.count) sum from

			(
				SELECT f.building,
						f.floor,
						l.bf,
						l.name l_name,
						l.lat,
						l.lon,
						l.lid cid,
						count(l.id)count 
					FROM floors f,
						layer_points l 
					where f.id=l.bf and 
							f.building<>0 and 
							l.lid=$cid and 
							f.building=$id 
							group by l.bf) a

				group by a.bf

			)a,layers c where a.cid=c.id";
			
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new allcategories();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_layers($condition,$extra=""){
	global $connection;
	require_once("layers.class.php");
	$sql = "SELECT * FROM `layers`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new layers();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_locations($condition,$extra=""){
	global $connection;
	require_once("locations.class.php");
	$sql = "SELECT * FROM `locations`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new locations();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_settings($condition,$extra=""){
	global $connection;
	require_once("settings.class.php");
	$sql = "SELECT * FROM `settings`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new settings();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_styles($condition,$extra=""){
	global $connection;
	require_once("styles.class.php");
	$sql = "SELECT * FROM `styles`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new styles();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_svg_info($condition,$extra=""){
	global $connection;
	require_once("svg_info.class.php");
	$sql = "SELECT * FROM `svg_info`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new svg_info();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_tiler($condition,$extra=""){
	global $connection;
	require_once("tiler.class.php");
	$sql = "SELECT * FROM `tiler`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new tiler();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_tour_cat($condition,$extra=""){
	global $connection;
	require_once("tour_cat.class.php");
	$sql = "SELECT * FROM `tour_cat`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new tour_cat();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_tour_poi($condition,$extra=""){
	global $connection;
	require_once("tour_poi.class.php");
	$sql = "SELECT * FROM `tour_poi`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new tour_poi();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_tours($condition,$extra=""){
	global $connection;
	require_once("tours.class.php");
	$sql = "SELECT * FROM `tours`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new tours();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

function getList_vertex($condition,$extra=""){
	global $connection;
	require_once("vertex.class.php");
	$sql = "SELECT * FROM `vertex`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	$list = array();
	if (!$result) return $list;
	while($row = $result->fetch_assoc()){
		$o = new vertex();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
	
}

?>