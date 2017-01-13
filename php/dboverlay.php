<?php
#
# Database overlay (v7.5).
# 
# All database + project helper functions go here
# 
# As a general RULE:
# 
# - This file ASSUMES THAT THE PATH IS ALREADY SETUP (since v3.1)
#
# - This file should NOT have ANY required files other than
# the database ones... If a function has other requirements
# it has to be moved to the mymapslib.php 
# 
include_once("db/connection.php");
include_once("db/db.php");
include_once("db/locations.class.php");
include_once("db/layers.class.php");
include_once("db/settings.class.php");
include_once("vertex.class.php");
include_once("floors.class.php");
include_once("categories.class.php");
include_once("edges.class.php");
include_once("edge_points.class.php");
include_once("layer_points.class.php");
include_once("tours.class.php");
include_once("tour_cat.class.php");
include_once("tour_poi.class.php");
include_once("aliases.class.php");
include_once("tiler.class.php");
include_once("styles.class.php");
include_once("svg_info.class.php"); 
include_once("allcategories.class.php"); 
# Map modes... 
# FIXME: Use only pixels and convert on the server...
$PIXEL_MODE=2;
$GPS_MODE=1;

# Double Acceptable error
$DOUBLE_ERROR=0.00001; 

# Categories types
$CAT_TYPE_NOLAYER 	= 1; 	// The category is not a layer
$CAT_TYPE_CLOSEST	= 2; 	// Is type of "closest"
$CAT_TYPE_ROUTENOW	= 4; 	// Instantly route on click rather than list locations
$CAT_TYPE_ENLAYER	= 8; 	// On click enable the layer also
$CAT_TYPE_NOAUTO	= 16;	// Exclude from auto complete... (used for Lxxx)
$CAT_TYPE_DISABLED	= 32;	// Exclude from directory (any recursion on cats)
$CAT_TYPE_BUBBLEUP	= 64;	// Bubble up to the overview
$CAT_TYPE_ALWAYSON	= 128;	// Always on layer
$CAT_TYPE_AUTODISPLAY	= 256;	// Always display category sub-title on autocomplete
$CAT_TYPE_DISPLAYTEXT	= 512;	// Always display category name for this location

# Status
$STATUS_OK 	= 0;
$STATUS_WARN	= 1;
$STATUS_DIS	= 2;

# Layer's table type
$LAYER_ENABLED	= 0;
$LAYER_DISABLED	= 1;
$LAYER_ALWAYSON = 2;

# Floor type MASKS
$FLOOR_OUTDOOR_PIN   	= 1;	// Display outdoor pin
$FLOOR_LIST_OVERVIEW 	= 2;	// Show floor on overview floorplan list
$FLOOR_SHOW_FLOOR 	= 4;	// Use `floor` field in the floor name for the list

# Styles table consts
$NOSTYLE=1;

# Inclination
$MAX_INC = 100;
$SMALL_INC=2;		// 1:20 (1:40 = 1.43209618) -> 0 - 1:20
$MEDIUM_INC= 5;		// 1:12 -> 0-1:12
$LARGE_INC=10;		// >1:12 (any) kg - changed from 90


//Admin functions ************************

function getCIDMap($condition,$extra=""){
	global $connection;
	require_once("mapping.class.php");
	$sql = "SELECT * FROM `mapping`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	if (!$result) return "";
	
	//die("Error... : getList_mapping");
	$list = array();
	while($row = $result->fetch_assoc()){
		$o = new mapping();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
}

function getSVGInfo($condition,$extra=""){
	global $connection;
	require_once("svg_info.class.php");
	$sql = "SELECT * FROM `svg_info`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	if (!$result) return "";
	
	//die("Error... : getList_mapping");
	$list = array();
	while($row = $result->fetch_assoc()){
		array_push($list,$row);
	}
	return $list;
}

function getSVGInfoO($condition,$extra=""){
	global $connection;
	require_once("svg_info.class.php");
	$sql = "SELECT * FROM `svg_info`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	if (!$result) return "";
	
	//die("Error... : getList_mapping");
	$list = array();
	while($row = $result->fetch_assoc()){
	    $tmp = new svg_info();
	    $tmp->setRow($row);
	    $tmp->_new=FALSE;
	    array_push($list,$tmp);
	}
	return $list;
}


function getSVGInfo_new($condition,$extra=""){
	global $connection;
	require_once("svg_info.class.php");
	$sql = "SELECT * FROM `svg_info`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	if (!$result) die("Error... : getList_master");
	$list = array();
	while($row = $result->fetch_assoc()){
		$o = new svg_info();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
}



//***************************

function getList_master($condition,$extra=""){
	global $connection;
	require_once("master.class.php");
	$sql = "SELECT * FROM `master`";
	if (isset($condition) && strcmp($condition,'')!=0) $sql = $sql." WHERE $condition";
	$sql = $sql." ".$extra;
	$result = $connection->send_query($sql);
	if (!$result) die("Error... : getList_master");
	$list = array();
	while($row = $result->fetch_assoc()){
		$o = new master();
		$o->setRow($row);
		$o->_new=false;
		array_push($list,$o);
	}
	return $list;
}


//**************************************
//End of admin functions ***************
/***************************************/

#
# Check that the PPM value is set and valid
#
function hasPPM($floor){
  $ppm=$floor->get_ppm();
  
  // Todo: Check that the overview will never have exactly 
  // 1 ppm value...
  if ($ppm > 0 && $ppm != 1) return TRUE;
  
  return FALSE;
}

#
# Get the floor config from floor, building
#
function getFloor($b, $f, $r=""){
  $where = "floor='$f' AND building='$b'";
  
  if ($r!="") $where.=" AND room='$r'";
  
  $floors = getList_floors($where, "LIMIT 1");
  if (count($floors)==0) return new floors();
  return $floors[0];
}

function getFloorsIdBasedMap(){

    $af=getList_floors("");
    $afm=array();
    foreach ($af as $f){
      $afm[$f->id] = $f;
    }
    
    return $afm;
}


#
# Return all floors sorted
#
function getAllFloorsSorted($overview=true){
  $sql = "1 ORDER BY description, floor";
  if (!$overview) $sql = "1 ORDER BY building, floor";
  $floors = getList_floors($sql);
  return $floors;
}

#
# Return all floors sorted
#
function getAllBuildingsSorted(){
  global $connection;
  $sql = "SELECT building,description FROM floors GROUP BY building";
  $res = $connection->send_query($sql);
  
  if (!$res){
    return array();
  }
  $buildings=array();
  while (($row = $res->fetch_assoc()) != false){
    $buildings[$row["building"]] = $row["building"];
//     $description=$row["description"];
//     if ($description!=""){
//       $buildings[$row["building"]] .= " - ".$description;
//     }
  }
  $connection->free($res);
  return $buildings;
}

function getBuildingsOpts(){
  $bs = getAllBuildingsSorted();
  
  $opts="";
  foreach ($bs as $id=>$b){
    $opts.="<option value='$id'>$b</option>";
  }
  
  return $opts;
}

#
# Return a floor for a room's name. FALSE if 
# it does not exist
#
function getFloorPlanForRoom($loc){
  if ($loc->_new) return FALSE;
  if ($loc->name=="") return FALSE;
  
  // Get all aliases
  $al = getAliases($loc->id);
  
  $where = "(room='".$loc->name."' ";
  foreach ($al as $a){
    $where .= "OR room='".$a->name."' ";
  }
  $where .=") ";
  
  $floors = getList_floors("$where", "LIMIT 1");
  if (count($floors)==0) return FALSE;
  return $floors[0];
}

#
# Return a floor for a building's name. FALSE if 
# it does not exist. Only consider floors shown in 
# the overview...
# 
# TODO: Description based select! TEST
#
function getFloorPlanForBuilding($loc){
  if ($loc->_new) return FALSE;
  if ($loc->name=="") return FALSE;

  // Get all aliases
  $al = getAliases($loc->id);
  
  $where = "(building='".$loc->name."' ";
  foreach ($al as $a){
    $where .= "OR building='".$a->name."' ";
  }
  $where .= "OR description LIKE '$loc->name,%' ";
  $where .=") ";
  $floors = getList_floors("$where AND ( type&2 )", "LIMIT 1");
  
  if (count($floors)==0) return FALSE;
  return $floors[0];
}


#
# Find and return the overview floor
#
function getOverviewFloor(){
  $f = getList_floors("building='0' AND floor='1'");
  if (count($f)==0) return new floors();
  return $f[0];
}

//KG added 3-/9/2013 - needed to reset locName field
//find match of name in description field
function getLocFromDesc($name) {
  $loc = getList_locations("description='$name'", "LIMIT 1");
  if (count($loc)==0) return new locations();
  return $loc[0];
}


#
# By Name (searches into aliases too). If there is an alias
# the field 'orig' is added to show the original location's
# name (loc_name).
# 
#
function getLocFromName($name){
  // $name = name2DBFormat($name);
  
  if ($name=="") return new locations();
  
  // Search first aliases
  $al = getList_aliases("name='$name'", "LIMIT 1");
  // If found return the location
  if (count($al)>0) {
    // NO NEED TO Make sure that the alias is correct
    // since we have on delete cascade
    $tmp_l = new locations($al[0]->get_lid());
    
    $tmp_l->orig = $tmp_l->name;
    $tmp_l->name = $name;
    return $tmp_l;
  }
  
  // No alias found... search all the locations
  $loc = getList_locations("name='$name'", "LIMIT 1");
  if (count($loc)==0) return new locations();
  return $loc[0];
}

/**
 * Convert specia html characters to DB format. 
 * For the reverse use urlencode... (to pass as ? query)
 */
function name2DBFormat($name){
  $name=trim($name);
  // First url decode to get rid of %XX characters
  $name=urldecode($name);

   // Then convert from whatever the enc. to html...
  $name=htmlentities($name, ENT_QUOTES, "UTF-8");
  
  return $name;
}

/**
 * Convert specia html characters to DB format. 
 * For the reverse use urlencode... (to pass as ? query)
 */
function DBFormat2name($name){
  $name=trim($name);
   // Then convert from whatever the enc. to html...
  $name=html_entity_decode($name,ENT_QUOTES);
  return $name;
}

#
# Get all aliases of a location
#
function getAliases($lid){
  $al = getList_aliases("lid='$lid'");
  return $al;
}

#
# Get all locations as a MAP based on their ID
#
function getMap_locations($where="", $extra=""){
  $list = getList_locations($where, $extra);
  $map = array();
  
  foreach ($list as $l){
    $map[$l->id] = $l;
  }
  
  return $map;
}

#
# Get map of all locations having $q in their name or their aliases
# (Used by autocomplete)
#
function getLocAliasMap($q){
  global $connection;
  
  if ($q=="") return getMap_locations("");
  
  $sql = "SELECT l.* FROM locations l, aliases a WHERE l.id=a.lid AND a.name  LIKE '%$q%'";

  $res = $connection->send_query($sql);
  $ret=array();
  
  if ($res===FALSE) return $ret;
  
  while (($row = $res->fetch_assoc()) != false){
    $tmp = new locations();
    $tmp->setRow($row);
    $tmp->_new = false;
    $ret[$tmp->id] = $tmp;
  }
  
  $connection->free($res);
  
  $ret += getMap_locations("name  LIKE '%$q%'");

  return $ret;
}

#
# By QR_ID
#
function getLocFromQR($qr_id){
  if ($qr_id==0) return new locations();
  
  // Search first aliases
  $al = getList_aliases("qr_id='$qr_id'", "LIMIT 1");
  // If found return the location
  if (count($al)>0) {
    // NO NEED TO Make sure that the alias is correct
    // since we have on delete cascade
    return new locations($al[0]->get_lid());
  }
  
  
  $loc = getList_locations("qr_id='$qr_id'", "LIMIT 1");
  if (count($loc)==0) return new locations();
  return $loc[0];
}

function getLayerFromName($name){
  $l = getList_layers("name='$name'", "LIMIT 1");
  if (count($l)==0) return new layers();
  return $l[0];
}

function getCatFromName($name){
  $c = getList_categories("name='$name'", "LIMIT 1");
  if (count($c)==0) return new categories();
  return $c[0];
}




#
# Get all the layer points of a specific layer and building/floor
#
function getLayerPointsFromBF($lid, $b, $f){
  $floor = getFloor($b, $f);
  return getLayerPointsFrom($lid, $floor);
}

#
# Get Enabled Layers (API helper function)
#
function getEnabledLayers(){
  return getList_layers("type%2=0","");
}

#
# Get Disabled Layers (API helper function)
#
function getDisabledLayers(){
  return getList_layers("type%2=0","");
}

#
# Get all the layer points of a specific layer and building/floor
#
function getLayerPointsFrom($lid, $floor){
  $where = "lid=$lid AND bf=".$floor->id;
  return getList_layer_points($where);
}

function getBuildingLayerPointsFrom($name,$cid){
  $floors=getList_floors("description='$name'");
  if (count($floors)==0) return FALSE;
  return getList_building_layer_points($floors[0]->building,$cid);
}
#
# Get child cats from a category
#
function getChildCategories($fcat){
   return getList_categories("pid=".$fcat->id." AND pid<>id ");
}


#
# Get child cats from a category, SORTED
#
function getChildCategoriesSorted($fcat){
   return getList_categories("pid=".$fcat->id." AND pid<>id ", "ORDER BY name");
}

#
# Get child cats from a category only if they are
# enabled as layers...
#
function getChildLayerCats($fcat_id){
   return getList_categories("pid=$fcat_id AND pid<>id AND type%2=0");
}

#
# Get parent category of a category...
#
function getParentCategory($fcat){
  $all_c = getList_categories("id=".$fcat->pid);
  
  if (count ($all_c) == 0)
    return new categories();
  
  return $all_c[0];
}

#
# Get categories that are enabled as layer! 
# [type%2=0, 1,3,5,.. etc work only as categories]
#
function getLayerCats(){
  return getList_categories("type%2=0","");
}

function getEntranceForLocation($loc){
  global $connection, $fconf;
  
  $ename=$loc->get_entr();
  if (substr($ename,0,strpos($ename,",")) > 0){
    $ename=substr($ename,0,strpos($ename,","));
  }
  if ($ename=="") return FALSE;
  $l = getLayerFromName("Entrances");
  $where = "lid=$l->id AND bf=".$fconf->id." AND name='$ename'";
  
  $lp = getList_layer_points($where);
 
  if (count($lp)==0)
    return FALSE;
  
  
  return $lp[0];
  
}


#
# CATEGORIES
#
function isRootCat($cat){
  return ($cat->id==$cat->pid);
}

/** NEVER USED... SEEMS TO SUPPORT MULTIPLE CATEGORIES FOR EACH LOCATION!!! **/
function getCatsForLocation($loc_id){
  global $connection;
  $res = $connection->send_query("SELECT c.* FROM categories c, catloc cl WHERE c.id=cl.cid AND cl.lid=$loc_id;");
		      
  if ($res===FALSE) return FALSE;
	  
  $i=0;
  $all_cat=array();
  while (($row = $res->fetch_assoc()) != false){
    $tmp = new categories();
    $tmp->setRow($row);
    $tmp->_new = false;
    $all_cat[$i] = $tmp;
    $i++;
  }
  
  $connection->free($res);
  return $all_cat;
}

function getCatsIdBasedMap(){

    $all_cats=getList_categories("");
    $all_cats_map=array();
    foreach ($all_cats as $c){
      $all_cats_map[$c->id] = $c;
    }
    
    return $all_cats_map;
}

#
# Returns the locations in the category with id=$cid
# recursively (so returns children too)... 
# 
# This function will not return child locations from categories
# with 0x16 mask
#
function getLocsForCategory($cid, $fconf=NULL){
  global $connection;
  
  $fcat = new categories($cid);
  
  if ($fcat->type&16) return FALSE;
  
  // Add bf id if needed...
  $where="cid=$cid";
  if ($fconf!=NULL) $where.=" AND bf=".$fconf->id;
  
  $all_loc = getList_locations($where);
  if ($all_loc===FALSE) return FALSE;
  
  
  // recursively add all the bellow categories=
  $level_cat = getChildCategories($fcat);
  foreach ($level_cat as $cc){
    $sub_loc=getLocsForCategory($cc->id, $fconf);
    $all_loc=array_merge($all_loc, $sub_loc);
 
  }
  
  return $all_loc;

}

#
# Returns all the locations in the category with id=$cid
# recursively (so returns children too). If the floor is
# given, then only locations of this floor will be returned
#
function getAllLocsForCategory($cid, $fconf=NULL){
  global $connection;
  
  
  // Add bf id if needed...
  $where="cid=$cid";
  if ($fconf!=NULL) $where.=" AND bf=".$fconf->id;
  
  $all_loc = getList_locations($where);
  if ($all_loc===FALSE) return FALSE;
  
  
  // recursively add all the bellow categories
  $fcat = new categories($cid);
  $level_cat = getChildCategories($fcat);
  foreach ($level_cat as $cc){
    $sub_loc=getLocsForCategory($cc->id, $fconf);
    $all_loc=array_merge($all_loc, $sub_loc);
 
  }
  
  return $all_loc;

}

#
# Returns all the locations in the category with id=$cid
# recursively (so returns children too). If the floor is
# given, then only locations of this floor will be returned
#
function getBuildingAllLocsForCategory($name,$cid){
  global $connection;
  $floors=getList_floors("description='$name'");
  if (count($floors)<1) return FALSE;
  
  $all_loc = getList_allcategories($floors[0]->building,$cid);
  //file_put_contents("/var/www/RL3/php.log","building = ".$floors[0]->building."\t",FILE_APPEND);
  //file_put_contents("/var/www/RL3/php.log","cid = ".$cid."\t",FILE_APPEND);
  //file_put_contents("/var/www/RL3/php.log","all_loc len = ".count($all_loc)."\n",FILE_APPEND);
  
  if ($all_loc===FALSE) return FALSE;
  
  return $all_loc;

}

function getBuildingAllLocs(){
  global $connection;
	$all_loc = getList_buildinglocations();
	//file_put_contents("/var/www/RL3/php.log","building_all_loc len = ".count($all_loc)."\n",FILE_APPEND);
  
  if ($all_loc===FALSE) return FALSE;
  
  return $all_loc;

}
#
# Returns all the locations in the category with id=$cid
# recursively (so returns children too). The locations 
# returned do not bellong on the overview...
# 
# Optionally pass the overview id (so we do not query it
# all the time on recursion)
#
function getLocsForCategoryNotOnOverview($cid,$ovid=NULL){
  global $connection;
  
  if ($ovid==NULL){
    $ov = getOverviewFloor();
    $ovid = $ov->id;
  }
  
  // Add bf id if needed...
  $where="cid=$cid";
  if ($fconf!=NULL) $where.=" AND bf<>".$ov->id;
  
  $all_loc = getList_locations($where);
  if ($all_loc===FALSE) return FALSE;
  
  
  // recursively add all the bellow categories
  $fcat = new categories($cid);
  $level_cat = getChildCategories($fcat);
  foreach ($level_cat as $cc){
    $sub_loc=getLocsForCategoryNotOnOverview($cc->id, $ovid);
    $all_loc=array_merge($all_loc, $sub_loc);
 
  }
  
  return $all_loc;

}

#
# Category code generators for admin version - writes to different divs
#
function admin_getCatsAsLinks($from, $depth=0, $cdepth=0, $level=0, $pcat=""){
  global $connection;
  $fcat = getCatFromName($from);
  
  // get Parent
  if ($pcat==""){
      $pcat=getParentCategory($fcat)->name;
  }
  
  if ($fcat->_new) return ""; // Not found
  if ($depth>0 && $depth+1==$cdepth) return "";
  
  // Print this level
  $ret="<a href='#".$fcat->name."' class='admin_cat-select level-".$level."' data-value='".$fcat->id."'".
	" data-value2='$pcat' data-value3='".$fcat->type."'>".$fcat->name."</a>";
  $level += 1;

  $level_cat=array();
  // These are closest only categories
  if ($level_cat!=6 && $level_cat!=7)
    $level_cat = getChildCategoriesSorted($fcat);

  foreach ($level_cat as $cc){
    $ret.=admin_getCatsAsLinks($cc->name, $depth, $cdepth+1, $level, $fcat->name);
  }
  
  return $ret;
}

#
# Category code generators
#
function getCatsAsLinks($from, $depth=0, $cdepth=0, $level=0, $pcat=""){
  global $connection,$CAT_TYPE_DISABLED;
  $fcat = getCatFromName($from);
  
  // Disabled category...
  if ($fcat->type&$CAT_TYPE_DISABLED) return "";
  
  // get Parent
  if ($pcat==""){
      $pcat=getParentCategory($fcat)->name;
  }
  
  if ($fcat->_new) return ""; // Not found
  if ($depth>0 && $depth+1==$cdepth) return "";
  
  // Print this level
  $ret="<a href='#".$fcat->name."' class='cat-select level-".$level."' data-value='".$fcat->id."'".
	" data-value2='$pcat' data-value3='".$fcat->type."'>".$fcat->name."</a>";
  $level += 1;


  $level_cat = getChildCategoriesSorted($fcat);

  foreach ($level_cat as $cc){
    $ret.=getCatsAsLinks($cc->name, $depth, $cdepth+1, $level, $fcat->name);
  }
  
  return $ret;
}


# From is the name of the top category
function getCatsAsOptions($from="ROOT", $depth=0, $cdepth=0, $level="", $altRoot=""){
  global $connection;
  $fcat = getCatFromName($from);
  
  if ($fcat->_new) return ""; // Not found
  if ($depth>0 && $depth+1==$cdepth) return "";
  
  // Print this level
  if ($altRoot) $fcat->name = $altRoot;
  $ret="<option value='".$fcat->id."'>".$level.$fcat->description."</option>";
  $level.="--";

  $level_cat = getChildCategories($fcat);

  foreach ($level_cat as $cc){
    $ret.=getCatsAsOptions($cc->name, $depth, $cdepth+1, $level);
  }
  
  return $ret;
}

//Not finished.......need to complete

function getCatsAsJSON($from="ROOT", $depth=0, $cdepth=0, $level=0){
  global $connection;
  $fcat = getCatFromName($from);
  
  if ($fcat->_new) return ""; // Not found
  if ($depth>0 && $depth+1==$cdepth) return "";
  
  $ret = "{LEVEL:".$level."},{CATEGORY:".$fcat->name."}";
  
  $level++;	
  $level_cat = getChildCategories($fcat);

  foreach ($level_cat as $cc){
    $ret .= getCatsAsJSON($cc->name, $depth, $cdepth+1, $level)."},";
  }
 
//  $ret = json_encode($ret);
  return $ret;
}

#
# Styles
#
function getStylesAsOptions(){
  global $NOSTYLE;
  $sts = getList_styles();
  if (!$sts) return;
  $opts="<option value=''>Select One</option>";
  
  foreach ($sts as $st){
    if ($st->id==$NOSTYLE) continue;
    $opts.="<option value='$st->id'>$st->name</option>";
  }
  
  return $opts;
  
}

function getStylesIdBasedMap(){
  $sts = getList_styles();
  $map=array();
  
  foreach ($sts as $st){
    $map[$st->id]=$st;
  }
  
  return $map;
}


#
# LOCATION BASED FUNCTIONS
#
function getVertexFromPos($lat, $lon, $bf){
  global $DOUBLE_ERROR,$connection;
  $res = getList_vertex("ABS(lat-$lat) < $DOUBLE_ERROR AND ABS(lon-$lon) < $DOUBLE_ERROR AND bf=$bf");
  
  if (count($res) != 1) return FALSE;

  return $res[0];
}

function getVertexFromPosLoose($lat, $lon, $bf, $error){
  global $connection;
  // could not make it work with DOUBLE_ERROR...
  $res = getList_vertex("ABS(lat-$lat) < $error AND ABS(lon-$lon) < $error AND bf=$bf");
  
  if (count($res) != 1) return FALSE;

  return $res[0];
}


function getLocationFromPos($lat, $lon, $bf){
  global $DOUBLE_ERROR,$connection;
  $res = getList_locations("ABS(lat-$lat) < $DOUBLE_ERROR AND ABS(lon-$lon) < $DOUBLE_ERROR AND bf=$bf");
  
  if (count($res) != 1) return FALSE;

  return $res[0];
}




#
# ENTRANCES
#
function getAllEntrances($where=""){
  global $connection;
  
  $layer = layer::select("name='Entrances'",$connection);
  if ($where=="")
    $all_e = layer_point::select("layer_id=".$layer[0]->id ,$connection);
  else
    $all_e = layer_point::select("$where AND layer_id=".$layer[0]->id ,$connection);
  
  return $all_e;
}

function getEntranceById($id){
  global $connection;
   
  return new layer_points($id);
}



function getEdgesFromEndPoints($v1, $v2){
  global $connection;
  

  $where= "   (start=".$v1->id." AND end=".$v2->id.") ";
  $where.= "OR (start=".$v2->id." AND end=".$v1->id.") ";
  
  $ns = array();
  
  $res = getList_edges($where);
  return $res;
}

#
# VERTEX RELATED
#
function getVertexFor( $type,$name){
  global $connection;
  
  $res = $connection->send_query("SELECT v.* FROM vertex v, layer_points lp WHERE  v.assoc_type='$type' ".
		    " AND v.assoc_id = lp.id AND lp.name='$name' ;");
		    
  $row=$res->fetch_assoc();
  if (count($row)==1)
    return FALSE;
  
  $v = new vertex();
  $v->setRow($row);
  $v->_new = false;
  
  return $v;
}

#
# Get vertex associated with this location. Return FALSE if not found.
#
function getVertexForLoc_Name($name){
  global $connection;
  
  $res = $connection->send_query("SELECT v.* FROM vertex v, locations lp, aliases a WHERE  ".
		    "(v.assoc_type='l' AND v.assoc_id = lp.id AND lp.name='$name' OR (a.lid=lp.id AND a.name='$name'));");
  $row=$res->fetch_assoc();

  if (count($row)==1 || $row==NULL)
    return FALSE;
    
    
    
  $v = new vertex();
  $v->setRow($row);
  $v->_new = false;
  
  return $v;
}

#
# Return all vertices of a floor that have a link
# value like $link (ie. "s:" => "s:%")
#
function getVertexLinkLike($floor_id,$link){
  global $connection;
  
  $res = getList_vertex("link LIKE '$link%' AND bf = $floor_id");
  return $res;
}

#
# Get the next integer value for links like $link
#
function getNextLink($link){
  global $connection;
  
  $res = getList_vertex("link LIKE '$link%'");
  
  $max_next=1;
  
  foreach ($res as $r){
    $next = substr($r->link,3);
    if (is_numeric($next) && $next > $max_next){
      $max_next = (int)$next + 1;
    }
  }
  return $max_next;
}

//KG - added so all verticies searched - not just those that are linked to locations 
// TODO: WILL BE REMOVED! CALL THE GENERIC  getVertexFor( $type,$name) with type="%"
// function getVertexForLoc_Name_all($name){
//   global $connection;
//   $res = mysql_query("SELECT v.* FROM vertex v, locations lp WHERE  ".
// 		    " v.assoc_id = lp.id AND lp.name='$name' ;",$connection->db_link);
//   $row=$res->fetch_assoc();
//   if (count($row)==0)
//     return FALSE;
//     
//   $v = new vertex();
//   $v->setRow($row);
//   $v->_new = false;
//   
//   return $v;
// }

function getNeigboorsOfVector($v){
  global $connection;
  
  $where = "SELECT v.* FROM vertex v, edges e ";
  $where.= "WHERE (e.start=$v->id AND e.end=v.id) ";
  $where.= "OR (e.end=$v->id AND e.start=v.id) ";
  
  $ns = array();
  
  $res = $connection->send_query($where);
  
  if ($res===FALSE) 
    return $ns;
  while ($row = $res->fetch_assoc()){
    $n = new vertex();
    $n->setRow($row);
    $n->_new = false;
    array_push($ns,$n);
  }
  
  return $ns;
}

/**
 * Return the neighboors of verctor $v that
 * are not associated with anything.
 */
function getNeigboorsOfVector_pureVec($v){
  global $connection;
  
  $where = "SELECT v.* FROM vertex v, edges e ";
  $where.= "WHERE ((e.start=$v->id AND e.end=v.id) ";
  $where.= "OR (e.end=$v->id AND e.start=v.id)) AND v.assoc_type<>'l' ";
  
  $ns = array();
  
  $res = $connection->send_query($where);
  
  if ($res===FALSE) 
    return $n;
  while ($row = $res->fetch_assoc()){
    $n = new vertex();
    $n->setRow($row);
    $n->_new = false;
    array_push($ns,$n);
  }
  
  return $ns;
}

function getMap_vertex($where="", $extra=""){
  $list = getList_vertex($where, $extra);
  $map = array();
  
  foreach ($list as $v){
    $map[$v->id] = $v;
  }
  
  return $map;
}


#
# EDGE RELATED
# 
function getEdgesFloor($bf=null, $where=""){
  global $connection,$fconf;
  if ($bf == null)
    $bf = $fconf->id;
    
  $es = array();
  
  $sql = "SELECT e.* FROM edges e, vertex v WHERE e.start=v.id AND v.bf=$bf";
  if ($where!="") $sql.=" AND $where";

  $res = $connection->send_query($sql);
  
  if ($res===FALSE) 
    return $es;
  while ($row = $res->fetch_assoc()){
    $e = new edges();
    $e->setRow($row);
    $e->_new = false;
    array_push($es,$e);
  }
  $res->free();
  return $es;
  
}

#
# Get the maximum order of POI found in a tour
#
function getMaxOrder($tid){
  global $connection;
  
  $sql = "SELECT MAX(t.order),COUNT(t.order) FROM tour_poi t WHERE t.tid=$tid";
  $res = $connection->send_query($sql);
  
  $order=-1;
  
  if ($res===FALSE) return $order;
  
  while ($row = $res->fetch_assoc()){
    $order=$row[0];
    if ($order==0 && $row[1]==0) $order=-1;
  }
  
  $res->free();
  return $order;
}

#
# Return t.order, l.name lines for a given tour
#
function getTourPOIOrder($tid){
  global $connection;
  
  
  $sql = "SELECT p.id, p.order, l.name FROM tour_poi p, locations l WHERE p.tid='$tid' AND p.lid=l.id ORDER BY `order`";
  $res = $connection->send_query($sql);
  
  $ret = array();
  if ($res===FALSE) return $ret;
  
  while ($row = $res->fetch_assoc()){
    array_push($ret,$row);
  }
  
  $res->free();
  return $ret;
}

#
# Get the point of interest with given order from this tour
#
function getPOIFromTourOrder($tid, $order){
  $pois=getList_tour_poi("tid=$tid AND `order`=$order", "LIMIT 1");
  
  if (count($pois) > 0) return $pois[0];
  
  return new tour_poi();
}

#
# Re-Index order avoiding skipping numbers (starting from 0)
#
function reIndexOrder($tid){
  
  $all = getTourPOIOrder($tid);
  
  $order=0;
  
  foreach ($all as $p){
    
    if ($order==$p["order"]) {
      $order++;
      continue;
    }
    
    // Replace order
    $p2 = new tour_poi($p["id"]);
    $p2->set_order($order);
    $p2->save();
    $order++;
  }
}


//
// Tiler Status Helpers
//
function getTilerFromBFTarget($bf, $target, $create=false){
  global $connection;
  
  if ($bf=="" || $target=="") return;
  
  $t = getList_tiler("bf=$bf AND target='$target'");
  
  // Return if found
  if (count($t)>0) return $t[0];
  
  // Return null if not create
  if (!$create) return new tiler();
  
  
  // Create!
  $t = new tiler();
  $t->set_bf($bf);
  $t->set_target($target);
  $t->set_status("NEW");
  $t->save();
  
  // Reload
  $t = new tiler($connection->lastId());
}

/**
 * Update tiler high level:
 * - if no target, both m and w will be set 
 * - if no bf is given ALL will be set 
 */
function updateTiler($set, $bf="", $target="",$updateFailed=FALSE){

  global $connection;
  
  ensureTilerAllFloors();
  
  $sql = "UPDATE `tiler` SET $set ";
  $and = "";
  $where="";
  
  if ($bf!=""){
    $where .= " $and bf=$bf ";
    $and="AND";
  }
  if ($target!="" ){
    $where .= " $and target='$target' ";
    $and="AND";
  }
  
  // Refuse to update FAILED elements with this function
  // So install commands do not overwrite failures
  if (!$updateFailed && strpos($set,"OK")!==FALSE) {
    $where.= "$and status NOT LIKE 'FAILED%'";
    $and="AND";
  }
  
  if ($where!="") $sql.=" WHERE $where";
  
  $rc = $connection->send_query($sql);
  
  return $rc;
}

function ensureTilerAllFloors(){
  $floors=getList_floors("");
  foreach ($floors as $f){
    getTilerFromBFTarget($f->id,"m",true);
    getTilerFromBFTarget($f->id,"w",true);
  }
}

function getTilerStatus($building){
  global $connection;
  
  $sql="SELECT t.* FROM tiler t, floors f WHERE t.bf=f.id and f.building='$building'";
  

  $res = $connection->send_query($sql);
  $ret=array();
  
  if ($res===FALSE) return $ret;
  
  while (($row = $res->fetch_assoc()) != false){
    $tmp = new tiler();
    $tmp->setRow($row);
    $tmp->_new = false;
    $ret[$tmp->id] = $tmp;
  }
  
  $connection->free($res);
  return $ret;
}

function canTileProj(){
  // Get ongoing jobs
  $jobs = getList_tiler("`lock`=1", "LIMIT 1");
  
  if (count($jobs)>0) return FALSE;
  return TRUE;
}

function canTileFloor($tid){
  // Get ongoing jobs
  // If any is going on you cannot tile!
  // if mobile is tiling you cannot do web and the opposite
  $t = new tiler($tid);
  $jobs = getList_tiler("bf=$t->bf AND `lock`=1", "LIMIT 1");
  
  if (count($jobs)>0) return FALSE;
  return TRUE;
}

//
// ---- Check for OBSOLETE BELOW HERE -----
//
function getEdgeWithPointsArray($eid){
  // Get From DB
  $e = new edges($eid);
  if ($e->_new) return FALSE;
  
  // get vertices
  $s = new vertex($e->get_start());
  if ($s->_new) return;
  $t = new vertex($e->get_end());
  if ($t->_new) return FALSE;
  
  $allpts = array();
  // Add start
  array_push($allpts, $s);
  
  // get edge points
  $epz = getList_edge_points("eid=$eid", "ORDER BY id");
  
  // Arrange all points + vertices in order
  if (count($epz)>0){
    // Compare distances from start to arrange
    $d1 = getDistance($s, $epz[0]);
    $d2 = getDistance($s, $epz[count($eps-1)]);
    // reverse
    if ($d2<$d1) {
      $epz = array_reverse($epz);
    }
  }
  
  // Order is now fine... add them
  foreach ($epz as $ep){
    array_push($allpts, $ep);
  }
  // Add end
  array_push($allpts, $t);
  
  return $allpts;
}

function objToJSON($obj){
  $arr = array();
  foreach($obj as $key => $value) {
    if ($key=="connection") continue;
    if ($key=="autocommit") continue;
    if ($key=="UPDATE") continue;
    if ($key=="_new") continue;
    $arr["$key"] = $value;
  }
  
  return json_encode($arr);
}

function findVertexCloseTo($lat, $lon, $bf){
  global $connection;
  $allv = getList_vertex("bf=".$bf);
  
  $min_dist=1000000;
  $min_vert=-1;
  
  for ($i=0; $i<count($allv); $i++){
    $dist=sqrt(pow($lat-$allv[$i]->lat,2) + pow($lon-$allv[$i]->lon,2));
    
    if ($dist<$min_dist){
      $min_dist=$dist;
      $min_vert=$allv[$i];
    }
  }
  
  return $min_vert;
}



?>
