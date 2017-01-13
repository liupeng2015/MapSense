<?php
$new_paths = "..".PATH_SEPARATOR."../db";
set_include_path(get_include_path() . PATH_SEPARATOR . $new_paths);
include_once("mymapslib.php");
include_once('importconsts.php');
include_once('Tools.php');
include_once('MathTools.php');
include_once("dboverlay.php");

$VER="7.3";

error_reporting(E_ERROR);
$x_scale  = 1; 
$y_scale  = 1;
$x_offset = 0;
$y_offset = 0;
$verts = array();

// Load Configuration

/// CHECKs
if (!isset($_REQUEST["project"])){
    prtE( "Please enter project name");
    return;	 	  
}
if (!isset($_REQUEST["filename"])){
    prtE( "Please enter filename");
    return;	 	  
}
if (!isset($_REQUEST["building"])){
    prtE( "Please enter project building");
    return;	 	  
}

if (!isset($_REQUEST["floor"])){
    prtE( "Please enter project floor");
    return;	 	  
}

//flag to denote whether location name should be in "Building.floor.ltext" format rather than just ltext [0] = ltext, [1] = Warwick-liek format
$floor_type = ""; 
if (isset($_REQUEST["floor_type"])){
    $floor_type = $_REQUEST["floor_type"];
}

//flag to indicate whether this is an incremental update or not
$update = 0; 
if (isset($_REQUEST["update"])){
    $update = $_REQUEST["update"];
}

$project = $_REQUEST["project"];
$fname = $_REQUEST["filename"];
$building = $_REQUEST["building"];
$floor = $_REQUEST["floor"];
if (isset($_REQUEST['room'])){
	$room = $_REQUEST["room"];
}
else $room = "";

if (isset($_REQUEST['description'])){
	$description = $_REQUEST["description"];
}
else $description = $building;

$PROJECT_DIR=$PROC_DIR."/".$project."/".$building.$floor."/";
$conf=$CONF_DIR."/".$project."_db.conf";

// Paths
// $file = $PROJECT_DIR.$fname;
$file = $fname;

// Remember if the db was just created
$just_created=false;

// Testing SQL speed up
$vertex_insert="INSERT INTO `vertex` VALUES \n";
$edges_insert="INSERT INTO `edges` VALUES \n";
$svg_insert="INSERT INTO `svg_info` VALUES \n";
$loc_insert="INSERT INTO `locations` VALUES \n";
$aliases_insert="INSERT INTO `aliases` VALUES \n";
$edge_points_insert="INSERT INTO `edge_points` VALUES \n";

// Create a single text for G layers
$Gtxt=array();
$Gtxt["G1"]="";
$Gtxt["G2"]="";

// ---- Functions


function loadCats(){
  global $catMap;
  $allc = getList_categories();
  
  foreach ($allc as $c){
    $catMap[$c->name] = $c->id;
  }
}

function checkROOT(){
  $croot = getCatFromName("ROOT");
  if (!$croot->_new) return;
  
  $cat = new categories('');
  $cat->set_name("ROOT");
  $cat->set_pid(1);
  $cat->id=1;
  
  $cat->save();
}

function checkAddCategory($name, $parent=""){
  global $connection, $error;
  
  // Check
  $tmp_cat=getCatFromName($name);
  if (!$tmp_cat->_new) return $tmp_cat->id;
  
  // Add ...
  $cat = new categories('');
  
  $tmp_name = trim($name);
  $tmp_name = str_replace("&", "&amp;", $tmp_name);
  $tmp_name = str_replace("'", "&#39", $tmp_name);
  $cat->set_name($tmp_name);
  
  // Check root/parent
  if ($parent!=""){
  
    // Check parent
    $tmp_cat=getCatFromName($parent);
    if ($tmp_cat->_new){
      checkAddCategory($parent);
      $tmp_cat=getCatFromName($parent);
    }
    
    $cat->set_pid($tmp_cat->id);
  }else{
    $croot = getCatFromName("ROOT");
    $cat->set_pid($croot->id);
  }
  
  $cat->save();
  return $connection->lastId();
}

function checkAddLayer($name){
  global $connection, $error, $LAYERIMAGES, $LAYERS;
  
  // Check
  $tmp_l=getLayerFromName($name);
  if (!$tmp_l->_new) return $tmp_l->id;
  
  // Add ...
  $tmp_l = new layers('');
  
  $tmp_name = trim($name);
  $tmp_name = str_replace("&", "&amp;", $tmp_name);
  $tmp_name = str_replace("'", "&#39", $tmp_name);
  
  // Set name and image
  $tmp_l->set_name($tmp_name);
  $tmp_l->set_description($LAYERS[$tmp_name]["description"]);
  $tmp_l->set_image_name($LAYERIMAGES[$tmp_name]);
  
  $tmp_l->save();
  return $connection->lastId();
}


// Create database if needed

$con = mysqli_connect($db_host,$db_user,$db_pass);
$con->set_charset("utf8");  
if (!$con){
  prtE('Could not connect: ' . $connection->last_error());
  return;
}

prtSubSec( "Creating database");
if ($con->query("CREATE DATABASE `$project` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci")){
  prtSubSec( "Database created" );
  
  // Create database configuration
  //file_put_contents($conf, "$db_host\n$project\n$db_user\n$db_pass\n");

  $con->select_db($project);

  // Copy skeleton
  prtSubSec( "Copying skeleton... ");
  $lines = file(dirname(__FILE__)."/DB.skel.sql");
 
  $templine="";
  // Loop through each line
  foreach ($lines as $line)
  {
      // Skip it if it's a comment
      if (substr($line, 0, 2) == '--' || $line == '')
	  continue;
  
      // Add this line to the current segment
      $templine .= $line;
      // If it has a semicolon at the end, it's the end of the query
      if (substr(trim($line), -1, 1) == ';')
      {
	  // Perform the query
	  $con->query($templine) or prtE('Error performing query \''. $templine . '\': ' . mysqli_error($con));
	  // Reset temp variable to empty
	  $templine = '';
      }
  }
  
  
  $just_created=true;
}

$con->close();


// Connect
$connection = new connection($conf);
$connection->connect();
$GLOBALS["connection"] = $connection;

// Check we are connected
if (!$connection->isConnected()) {
	prtE('Connection->Could not connect: ' . $connection->last_error);
	return;
}


$connection->send_query("SET FOREIGN_KEY_CHECKS = 0;");

/**
 * Without the following, SQL will treat id 0 as 0. With
 * the following 0 will trigger next auto id
 */
$connection->send_query("SET sql_mode = '';");



// Check root category
checkROOT();

//Open up file and start reading
$first_e = 0;
$first_l = 0;
$last_vertex_db = $last_edge_db = $last_loc_db = 1;

prtSubSec("Opening file: ".$file);

$file_handle = fopen($file, "r");
if ($file_handle===FALSE){
  prtE("Error Opening file: ".$file);
  $connection->close();
  exit(0);
}
$old_content = file_get_contents($file);

prtSubSec("Loading categories");
loadCats();
prtSubSec("Importing into: ".$project);

while (!feof($file_handle)) {

    $line = fgets($file_handle);
    if (trim($line) == "") continue;
    $parts = explode(";", $line);     

    switch ($parts[0]) {
	
	case "o":
	  continue; // Do not add the line in $content!!! we will overwrite it
	  break;
	case "p":
	
		$w2g_project=$parts[1];
		$w2g_building=$parts[2];
		$w2g_floor=trim($parts[3]);   //space being introduced afterwards


		if ($w2g_project == $project) {
		  prtSubSec( "Correct project match");
		  if ($w2g_building == $building) {
		      prtSubSec( "Correct building match" );
		  }
		  if ($w2g_floor == $floor) {
			  
			  
		      prtSubSec( "Correct floor match");
		  }
		  prtSubSec( "Matching project details. Proceeding to import using location name format: ".$loc_format);
		  break;
		}			
		else {
		  prtE( "Non matching project details - import failed");
		  exit;
		}
		break;
	case "vb":
		$x_scale = ($parts[3]/$parts[1]); 
		$y_scale = ($parts[4]/$parts[2]); 
		prtSubSec ("SVG Canvas: ".$parts[1]." x.".$parts[2]);
		prtSubSec ("Image Canvas: ".$parts[3]." x ".$parts[4]);
		prtSubSec ("Scaling: X = ".$x_scale." Y= ".$y_scale);
		
		// Calculate expected offset
		$pow2x = upper_power_of_two((int)$parts[3]);
		$pow2y = upper_power_of_two((int)$parts[4]);
		$maxpow = $pow2x;
		if ($maxpow<$pow2y) $maxpow=$pow2y;
		
		// Maybe cast to int?
		$x_offset = ($maxpow - $parts[3])/2;
		$y_offset = ($maxpow - $parts[4])/2;
		prtSubSec ("Nearest tiled image size: width = ".$maxpow."px, height= ".$maxpow."px");
		prtSubSec ("Image offset: width = ".$x_offset."px, height= ".$y_offset."px");
	
		/* If this is a floorplan update, then don't do all this bit */
		if ($update==0) {    
			$add_floor = new floors('');
			$add_floor->set_width($parts[3]);
			$add_floor->set_height($parts[4]);
			$add_floor->set_mode(2);
			$add_floor->set_maxzoom(log($maxpow/256,2)); // Set the real one as log($pow2x/256,2). This is tile based, SVG needs more
			$add_floor->set_minzoom(2);
			$add_floor->set_room($room);
			$add_floor->set_floor($floor);
			$add_floor->set_building($building);
			$add_floor->set_description($description);
			if ($floor == "000") $floor_type = "2";	
			if ($floor_type != "") $floor_type = $floor_type;	
			if ($floor_type != "") $add_floor->set_type($floor_type);	
			$add_floor->set_tiles("projects/$project/tiles/w/$building$floor$room/");
			$add_floor->set_tile_type("png");
			$add_floor->save();
			$bf = $connection->lastId();
			if ($bf==0){
			  prtE("Cannot Enter Floor");
			  prtE($connection->last_error);
			  exit(1);
			}
			prtSubSec("Added floor ".$bf);
			
			
			// Check if we need to add settings 
			if ($just_created){
			  $con = mysqli_connect($db_host,$db_user,$db_pass);
			  $con->query("INSERT INTO settings (`id`,`dbver`) VALUES (1,'$VER');");
			  $con->close();
			}	
		}
		//end of new floorplan part, now work out which floorplan is being updated - get bf value
		if ($update == 1) {
		  $updated_floor = getFloor($building, $floor); 
		  $bf = $updated_floor->id;
		  prtSubSec( "Updating floor:".$bf);
		}
		//Find out the last database entries to store in w2g for SVG creation
		//insert dummy record to locations, edge and vertex table in order to get the id field - make db routine for this!
		$sql= "INSERT INTO `vertex` (`bf`) VALUES ('$bf')";
		if (!$connection->send_query($sql))
		  prtSubSec ("Error writing to vertex table: " . $connection->last_error."\n");
		$v_offset=$last_vertex_db=$connection->lastId();
		
		$sql= "INSERT INTO `edges` (`stairs`) VALUES ('N')";
		if (!$connection->send_query($sql))
		  prtSubSec( "Error writing to edge table: " . $connection->last_error."\n");
		$e_offset=$last_edge_db=$connection->lastId();
		
		$sql= "INSERT INTO `locations` (`name`,`bf`,`cid`) VALUES ('Dummy_location', $bf, '1')";
		if (!$connection->send_query($sql))
		  prtSubSec("Error writing to location table: " . $connection->last_error."\n");
		  
		$l_offset=$last_loc_db=$connection->lastId();
		
		prtSubSec("v,e,l offsets: ".$v_offset." ".$e_offset." ".$l_offset);
		break;
	// PPM, update floor values
	case "PPM":
		$f = new floors($bf);
		$f->set_upm($parts[1]);
		$f->set_ppm($parts[2]);
		$f->save();
		break;
	//Don't store in db....use W2G file to recreate the general layer for the SVG
	case "G1":
	case "G2":
		// Store G string ;)
// 		$Gtxt[$parts[0]].=$parts[1];
		$Gtxt[$parts[0]].= preg_replace('/^G[1,2];/',"",$line);
		
		break;
	

	case "t":
		//t;1;1;0;0;1;820.9387;743.249;test2;2;'ArialMT';;1;0
		//t;2;1;0;0;1;820.9387;741.7021;test;2;'ArialMT';;1;1
		$svg = new svg_info('');
		$svg->set_type("T");
		$svg->set_bf($bf);
		$transform = $parts[2]." ".$parts[3]." ".$parts[4]." ".$parts[5]." ".$parts[6]." ".$parts[7];
		$svg->set_transform($transform);
		if ($usetext) $svg->set_value($parts[8]);
		else $svg->set_value("");//fill with empty string - this is populated after import
		$svg->set_fontsize($parts[9]);
		//$svg->set_fontfamily($parts[10]); //Need to Add to svg_info database first!...TO DO!
		$svg->set_fill($parts[11]);
		$svg->set_lid($parts[12]+$l_offset-$l_start);
		$svg->set_index($parts[13]);
		//$svg->save();
		$code_parts = explode("|", $svg->get_save_code());
		$svg_insert.= $code_parts[1].",";
		break;
	case "l":
		//prtSubSec( $parts[2]."\n");
		if ($first_l == 0) {
			$l_start = (intval($parts[1])-1);
			$first_l = 1;
		}
		
		$loc = new locations('');
		
		$name = $parts[2];
		
		
		if (substr($parts[2],0,3) == "Lxx") 
		  $name = "Lxx".($parts[1]+$l_offset-$l_start);
		  
		//zz loc txt
		//Provisions for different location naming conventions
		// OLD WAY!
// 		if ($loc_format == 0){
// 		$loc->set_name(name2DBFormat($name));
// 		}
// 		//only do this for locations that have ltext values
// 		//replace the "-" to "." to match up with the Warwick database format
// 		//eg building = "02-005"; floor = "000"; ltext = "045"
// 		//loc name = "02.005.000.045"  
// 		
// 		if ($loc_format == 1){
// 			$name = str_replace('-','.',$building).".".$floor.".".$parts[2];
// 			$loc->set_name(name2DBFormat($name));
// 		}
		/*
		// NEW WAY
		if (isset($ROOM_RENAME) && count($ROOM_RENAME)>0){
		  // Apply the rulez...
		  foreach($ROOM_RENAME as $rule){
		    // replace _b_ and _f_
		    $rep = str_replace("_b_",$building,$rule["replace"]);
		    $rep = str_replace("_f_",$floor,$rep);

		    $name = preg_replace ( $rule["pattern"], $rep , $name);
		  }
		}
		
		$testloc = getLocFromName(name2DBFormat($name));	 
		if (!$testloc->_new){
		   $name .= "_${building}_$floor";
		   prtSubSec("Duplicate:".$name."\n");
		}*/
		$loc->set_name(name2DBFormat($name));
		
		// Urban: NEW
		// Set the imported original name as description to be used
		// later as reference back to the master CSV (or DB)
		$loc->set_client_id(name2DBFormat($name));
		
	
		$loc->set_lat($parts[3]*$x_scale+$x_offset);
		$loc->set_lon($parts[4]*$y_scale+$y_offset);
		
		// Set correct cid to the location...
		$loc->set_cid(1);
		//l;1;SC_1_Perimeter;112.205;95.3905;0.75,167.893..... 0.75,167.893;#D3BF96;#FFFFFF;0.5;BO;

		$cname = getCatNameFromW2GType($parts[9]);
		if (isset($catMap[$cname])) {
		  $tmp_cid = $catMap[$cname];
		  $loc->set_cid($tmp_cid);
		}
		
		$loc->set_bf($bf);
		
		$polypoints = $parts[5];
		$points = explode(" ",$polypoints);
		$scaled_polypoints = "";
		foreach ($points as $each_point) {
			$Px_y = explode(",", $each_point);
			$Px= $Px_y[0];
			$Py= $Px_y[1];
			$scaled_polypoints .= ($Px*$x_scale+$x_offset).", ".($Py*$y_scale+$y_offset)."\n";
		}
		
		//don't import polypoints into locations database
		//if (trim($parts[9]) != "BO") $loc->set_polygon($scaled_polypoints);
 		$loc->set_polygon($scaled_polypoints);	

		$code_parts = explode("|", $loc->get_save_code());
		$loc_insert.= $code_parts[1].",";
		
		//Save in svg_info table to recreate SVG from this info
		$svg = new svg_info('');
		$svg->set_type("L");
		$svg->set_bf($bf);
		$svg->set_lid($parts[1]+$l_offset-$l_start);
		$svg->set_fill($parts[6]);
		$svg->set_color($parts[7]);
		$svg->set_stroke($parts[8]);	
		if (strlen($scaled_polypoints)<15000)
		  $svg->set_points($scaled_polypoints);
		else 
		  $svg->set_txt($scaled_polypoints);	
		// $svg->save();
		$code_parts = explode("|", $svg->get_save_code());
		$svg_insert.= $code_parts[1].",";	
		break;
		
    case "v":
		$count = $parts[1];
		$w2gv = w2gVertex::getFromParts($parts);
		$d_x = $w2gv->x; 
		$d_y = $w2gv->y;
		
		$w2gv->scale($x_scale, $y_scale);
		$w2gv->move($x_offset, $y_offset);	
		// save ids in order to calculate edge lengths
		$verts[$w2gv->id] = $w2gv; 

		$vertex = new vertex('');
		$vertex->set_lat($w2gv->x);
		$vertex->set_lon ($w2gv->y);
		$vertex->set_bf($bf);
		$vertex->set_link($w2gv->link);
		
		//Translate w2g format to database format...
		if (($w2gv->ass_t == "D") || ($w2gv->ass_t == "VD") || ($w2gv->ass_t == "CD")) {
		//v;1;-0.9941 0.1089 -0.1089 -0.9941 779.90254 461.49354;779.90254;461.49354;D;79;263.74838577061;
			$svg = new svg_info('');
			if ($w2gv->ass_t == "D") $svg->set_type("D");
			if ($w2gv->ass_t == "VD") $svg->set_type("VD");
			
			$svg->set_bf($bf);
			$d_rot = $w2gv->angle;
			//x,y coordinates for door svg info should not be scaled
			
			// transform is in this format: rotate(353.74838577062 789.31356,457.89034) -->rotation x y 			
			$transform = $d_rot." ".$d_x.",".$d_y;
	
			$svg->set_transform($transform);
			$svg->set_fill($door_fill);
			$svg->set_color($door_color);
			$svg->set_stroke($door_stroke);
			//$svg->set_value($parts[8]);
			//$svg->set_fontsize($parts[9]);
			$svg->set_lid($w2gv->ass_id+$l_offset-$l_start);
			//$svg->set_index($w2gv->index+$v_offset); // Changed here see email
			$svg->set_index($w2gv->id+$v_offset); //index is the vertex ID - not done like text (since text does not have a vertex in db)
			
			//$svg->save();
			$code_parts = explode("|", $svg->get_save_code());
			$svg_insert.= $code_parts[1].",";
			//prtSubSec("Added SVG info for doors");
		}
		//Entrances
		if (($w2gv->ass_t == "e")|| ($w2gv->ass_t == "be")) { //include bridge entrances
		//v;1;-0.9941 0.1089 -0.1089 -0.9941 779.90254 461.49354;779.90254;461.49354;D;79;263.74838577061;
			$svg = new svg_info('');
			if ($w2gv->ass_t == "e") $svg->set_type("E");
			if ($w2gv->ass_t == "be") $svg->set_type("BE");
			$svg->set_bf($bf);
			$d_rot = $w2gv->angle;
			//x,y coordinates for door svg info should not be scaled
			
			// transform is in this format: rotate(353.74838577062 789.31356,457.89034) -->rotation x y 			
			$transform = $d_rot." ".$d_x.",".$d_y;
	
			$svg->set_transform($transform);
			$svg->set_fill($entrance_fill);
			$svg->set_color($entrance_color);
			$svg->set_stroke($entrance_stroke);
			//$svg->set_value($parts[8]);
			//$svg->set_fontsize($parts[9]);
			$svg->set_lid($w2gv->ass_id+$l_offset-$l_start);
			//$svg->set_index($w2gv->index+$v_offset); // Changed here see email
			$svg->set_index($w2gv->id+$v_offset); //index is the vertex ID - not done like text (since text does not have a vertex in db)
			
			//$svg->save();
			$code_parts = explode("|", $svg->get_save_code());
			$svg_insert.= $code_parts[1].",";
			//prtSubSec("Added SVG info for doors");
		}
			
		$assoc="";
		if ($w2gv->ass_t == "CD") $assoc = "CD"; //corridor door verticies
		if ($w2gv->ass_t == "CC") $assoc = "CC"; //corridor points that can be dumped - don't want to do now in
		//case order and association messed up - TO DO
		if ($w2gv->ass_t == "D") $assoc = "D";
		if ($w2gv->ass_t == "VD") $assoc = "VD";
		if ($w2gv->ass_t == "TM") $assoc = "l";
		if ($w2gv->ass_t == "TF") $assoc = "l";
		if ($w2gv->ass_t == "TD") $assoc = "l";
		if ($w2gv->ass_t == "TO") $assoc = "l"; 	//toilet other (not known from CAD) or unisex 
		if ($w2gv->ass_t == "l") $assoc = "l";
		if ($w2gv->ass_t == "v") $assoc = "v";
		if ($w2gv->ass_t == "C") $assoc = "c"; 		//unprocessed corridor vertex
		if ($w2gv->ass_t == "SC") $assoc = "sc"; 	//snapped to guiderail corridor vertex
		if ($w2gv->ass_t == "JC") $assoc = "jc"; 	//snapped and joined with edge to other vertex on guiderail
		if ($w2gv->ass_t == "GG") $assoc = "gg"; 	//guiderail vertex joined 
		if ($w2gv->ass_t == "S") $assoc = "S";      //This was "d" for some reason! KG changed 24/9/2013 
		if ($w2gv->ass_t == "L") $assoc = "L";
		if ($w2gv->ass_t == "e") $assoc = "E";		//entrance
		if ($w2gv->ass_t == "be") $assoc = "E";		//entrance - don't distinguish between BE nd E in vertices table - only in SVGinfo
		$vertex->set_assoc_type ($assoc);
		
		$assoc_id_l = $w2gv->ass_id + $l_offset;
		$assoc_id_v = $w2gv->ass_id + $v_offset;
		//need to add offset. if location vertex, then need to add offset for locations
		
		 //use location offset
		if ($assoc == "l") $vertex->set_assoc_id ($assoc_id_l); 
		//use vertex offset  - assoc ID is a vertex
		else if (($assoc != "l") && ($assoc != "")) $vertex->set_assoc_id ($assoc_id_v);
		
		///put back in later to keep relationship of vertex - 
		
		$vertex->id = $v_offset + $w2gv->id;
		$code_parts = explode("|", $vertex->get_save_code());
		$vertex_insert.= $code_parts[1].",";
		
		
		// Now check if this vertex is also a layer
		if (isLayerw2gType($w2gv->ass_t) && getLayerNameForw2gType($w2gv->ass_t)!=""){
		
		  $layer_name =  getLayerNameForw2gType($w2gv->ass_t);
		  $layer_point = new layer_points('');
		  $layer_point->set_lid(checkAddLayer($layer_name));
		  $layer_point->set_lat($w2gv->x);
		  $layer_point->set_lon($w2gv->y);
		  $layer_point->set_bf($bf);
		  $layer_point->save();
		}
		
	
		break;
        
	case "e":
		$w2ge = w2gEdge::getFromParts($parts);
		$edge = new edges('');
		
		$val = ($w2ge->v1 + $v_offset);
		$edge->set_start($val);
		
		$val = ($w2ge->v2 + $v_offset);
		$edge->set_end($val);
		

		// Calculate length here, vertices are already scaled
		$length=$w2ge->length;
		if ($w2ge->length!=500){
		  $length = Point::dist($verts[$w2ge->v1], $verts[$w2ge->v2]);
		}else{
		  $length *= $x_scale;
		}
		$edge->set_length($length);
		$edge->set_stairs("N");
		
		$code_parts = explode("|", $edge->get_save_code());
		$edges_insert.= $code_parts[1].",";
		
		// NEW: Edge points...
		$epoints = trim($w2ge->points);
		if ($epoints!=""){
		  $epoints = explode(" ", $epoints);
		  foreach ($epoints as $ep){
		    $epcoords = explode(",", $ep);
		    // Make a point to scale and move
		    $w2gp = new Point($epcoords[0],$epcoords[1]);
		    $w2gp->scale($x_scale, $y_scale);
		    $w2gp->move($x_offset, $y_offset);
		    
		    // Create DB Object
		    $edge_p = new edge_points('');
		    $edge_p->set_eid($e_offset+$w2ge->id);    // Set edge
		    $edge_p->set_bf($bf);		// Set bf
		    $edge_p->set_lat($w2gp->x);
		    $edge_p->set_lon($w2gp->y);
		    
		    $code_parts = explode("|", $edge_p->get_save_code());
		    $edge_points_insert.= $code_parts[1].",";
		  }
		}
		
		
		break;
	case "LP":
		$lp = w2gLayerPoint::getFromParts($parts);
		$lp->scale($x_scale, $y_scale);
		$lp->move($x_offset, $y_offset);
		
		$layer_name =  getLayerNameForw2gType($lp->type);
		$layer_point = new layer_points('');
		$layer_point->set_lid(checkAddLayer($layer_name));
		$layer_point->set_lat($lp->x);
		$layer_point->set_lon($lp->y);
		$layer_point->set_bf($bf);
		$layer_point->save();
		break;
	case "A":
		$w2ga = w2gAlias::getFromParts($parts);
		$al = new aliases('');
		$al->set_lid($w2ga->lid + $l_offset); //KG added
		$al->set_name($w2ga->text);
		$code_parts = explode("|", $al->get_save_code());
		$aliases_insert.= $code_parts[1].",";
		break;
	default:
		//prtE("UnKnown Line: '$line'");
		break;
	}
}

// 1G limit
$connection->send_query("set global max_allowed_packet=1000000000;");

// Mass Save...
$vertex_insert[strlen($vertex_insert)-1]=";";
if (!$connection->send_query($vertex_insert)) prtE ( "Cannot insert vertices: ".$connection->last_error);

$edges_insert[strlen($edges_insert)-1]=";";
if (!$connection->send_query($edges_insert)) prtE ( "Cannot insert edges: ".$connection->last_error);

$loc_insert[strlen($loc_insert)-1]=";";
if (!$connection->send_query($loc_insert)) prtE ( "Cannot insert locations: ".$connection->last_error);


$edge_points_insert[strlen($edge_points_insert)-1]=";";
if (!$connection->send_query($edge_points_insert)) prtW ( "Cannot insert edge_points: ".$connection->last_error. " size=".strlen($edge_points_insert));

$aliases_insert[strlen($aliases_insert)-1]=";";
if (!$connection->send_query($aliases_insert)) prtW ( "Cannot insert aliases: ".$connection->last_error." (non-fatal)");

$svg_insert[strlen($svg_insert)-1]=";";
if (!$connection->send_query($svg_insert)) prtW ( "Cannot insert svg_info: ".$connection->last_error. " size=".strlen($svg_insert));

// Do general layers (TODO: for loop)
$svg = new svg_info('');
$svg->set_type("G1");
$svg->set_bf($bf);
$svg->set_txt($Gtxt["G1"]);
$svg->save();

$svg = new svg_info('');
$svg->set_type("G2");
$svg->set_bf($bf);
$svg->set_txt($Gtxt["G2"]);
$svg->save();




//now delete the dummy records
$sql = "DELETE FROM `vertex` WHERE `id` = ".$last_vertex_db;
if ($connection->send_query($sql)) prtSubSec ( "Vertices imported.");
else prtSubSec ( "Error: " . $connection->last_error());

$sql = "DELETE FROM `edges` WHERE `id` = ".$last_edge_db;
if ($connection->send_query($sql)) prtSubSec ( "Edges imported");
else prtSubSec ( "Error: " . $connection->last_error());

$sql = "DELETE FROM `locations` WHERE `id` = ".$last_loc_db;
if ($connection->send_query($sql)) prtSubSec ( "Locations imported" );
else prtSubSec ("Error: " . $connection->last_error());

fclose($file_handle);
$connection->send_query("SET FOREIGN_KEY_CHECKS = 1;");
prtSec("PROCESSING COMPLETE");

//Open up w2g file and insert the new vertex,edge and location offset 

//prtSubSec("Updating W2G file");
//prtSubSec("v,e,l offsets: ".$v_offset." ".$e_offset." ".$l_offset);

$file_handle = fopen($file, "w");
fwrite($file_handle,"o;".$v_offset.";".$e_offset.";".$l_offset ."\n".$old_content);	
fclose($file_handle);


?>

