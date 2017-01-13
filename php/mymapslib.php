<?php
include_once("fslib.php");
include_once("dboverlay.php");
include_once("libmath.php");
include_once("libgps.php");

# Paths to executables...
$connection_graph_file="boost/db_graph";
$connection_graph_file_cat="boost/db_graph_cat";
$connection_graph_file_tour="boost/db_graph_tour";

$warn_img="./rl_images/layers/warning.png";

# Thresshold in degrees, above that we consider to
# take a turn...
$STR8_THRES_DEG=50; 

// The distance threshold (in pixels). Smaller than this 
// the edge is NOT split but just connected
$SPLIT_VERTEX_THRES=5;


/// ESCAPE for use in JSON
function replaceSQouteForJSON($msg){
  $msg = str_replace("&#039;","\\\\\'",$msg);
//   $msg = str_replace("&#039;","\\\'",$msg);
  return $msg;
}


/**
 * Converting to clear text!
 */
function DB2name($name){
  $name=html_entity_decode($name, ENT_QUOTES|ENT_HTML5, "UTF-8");
  return $name;
}

function getAllFloorsSel(){
  $floors = getList_floors("1 ORDER BY description, floor");
  $opts="";
  foreach ($floors as $f){
	$opts.="<option value='$f->id'>".$f->description." (".$f->building."), ".$f->floor."</option>";
  }
  return $opts;
}

function getAllLocsForFloorSel($bf){

  $locs = getList_locations("bf=$bf");
  
  $opts="";
  $opts.="<option value=''>Select One</option>";
  foreach ($locs as $l){
    $opts.="<option value='$l->id'>".$l->get_name()."</option>";
  }
  return $opts;
}

function getPoIsOptionsFor($tid){

  $pois = getList_tour_poi("tid=$tid","ORDER BY `order`");
  
  $opts="";
  $opts.="<option value=''>选择感兴趣的地点</option>";
  foreach ($pois as $p){
    $loc = new locations($p->get_lid());
    $opts.="<option value='$p->id'>".$loc->get_name()."</option>";
  }
  return $opts;
}


/**
 * Check and draw all the selected layers
 *  
 *  @param directions Show or not the options "Directions From/To"
 */
function parseLayers($directions=TRUE){
  global $CAT_TYPE_ALWAYSON,$LAYER_ALWAYSON, $g_mapState,$fconf;
   
  //zz 暂时屏蔽总是显示的兴趣点
  // Now handle always on categories
  $cats = getList_categories("type&$CAT_TYPE_ALWAYSON=$CAT_TYPE_ALWAYSON");
  foreach ($cats as $c) {
    $_REQUEST["c__".str_replace(" ","_",$c->get_name())]="on";
  }
  
  // Now handle always on layers
  $cats = getList_layers("type&$LAYER_ALWAYSON=$LAYER_ALWAYSON");
  foreach ($cats as $c) {
    $_REQUEST["l__".str_replace(" ","_",$c->get_name())]="on";
  }
  
  $catlist_ = array();
  $catlist = array();
  foreach ($_REQUEST as $key => &$value) {
      // Warnings!
      if ($key == "c__Path_Warnings"){
	drawPathWarnings();
	continue;
      }
      // Normal Layers
      if ($value != "on" ) continue;
      
      
      $parts=explode("__",$key);
      $parts[1]=str_replace("_"," ",$parts[1]);
    
	if($fconf->building==0){
		array_push($catlist_,$parts[0]);
		array_push($catlist,$parts[1]);
	}
//	}else{	
      if ($parts[0]=="c") {
	drawCatLayer($parts[1],TRUE,$directions);
	$g_mapState->cat_layers[]=$parts[1];
	if (isOverview()) bubbleUpCatLayer($parts[1],TRUE,$directions);
      }
      else if ($parts[0]=="l") {
	drawLayer($parts[1]); 
	$g_mapState->layers[]=$parts[1];
      }
	  
//	  }
  }
    if(count($catlist)>0){
		$cc= array();
		$ll= array();
		for ($i= 0;$i< count($catlist); $i++){
			if ($catlist_[$i]=="c") {
				array_push($cc,$catlist[$i]);
				$g_mapState->cat_layers[]=$catlist[$i];
				if (isOverview()) bubbleUpCatLayer($catlist[$i],TRUE,$directions);
			}else if ($catlist_[$i]=="l") {
				array_push($ll,$catlist[$i]);
				$g_mapState->layers[]=$catlist[$i];
			}
			
		}
		drawBuildingCatLayer($cc,$ll,TRUE,$directions);
		//file_put_contents("/var/www/RL3/php.log",count($cc)."\n",FILE_APPEND);
	}
}


// Create Link onclick for location
function getOnClickLocLink($loc, $vr){
  global $mp_settings, $fconf, $config, $g_mapState,$cur_poi;
  
  if ($vr['used'] == FALSE) return "";
  
  
  
  $onclick = "window.location='".$_SERVER["SCRIPT_NAME"]."?config=$config&room=".urlencode(DB2name($loc->name))."&f=".$vr['next_f']."&b=".$vr['next_b'];
  if ($fconf->building != "0") {
    //keeps zooms when switching between floors
    $onclick.= "&zoom='+document.getElementById(\"zoom\").value+'"; 
    //keeps center when switching between floors
    $onclick.= "&map_center='+document.getElementById(\"map_center\").value+'"; 
  }else{
    //change zooms when switching between overview - floors 
    $onclick.= "&zoom='+(parseInt(document.getElementById(\"zoom\").value)+1)+'"; 
  }
  
  // From part
  if ($g_mapState->from!="")
    $onclick.="&from=".urlencode(DB2name($g_mapState->from));
    
  // New: ids
  $onclick.="&flid=$g_mapState->flid";
  $onclick.="&rlid=$g_mapState->rlid";
 
  // get entrance
  $en=getEntranceForLocation($loc);
  
  // initial_node_id
  if (isset($vr['to_node'])){
    $onclick.="&yah_node_id=".$vr['to_node'];
  }else if ($en!==FALSE){
    $onclick.="&yah_entr=".$en->name;
  }
  
  // Stairs/Elev. options
  $onclick.="&st_elev_gr=".$_REQUEST["st_elev_gr"];
  
  // In/Out option
  if (isset($_REQUEST['rscale']) )
    $onclick.="&rscale=".$_REQUEST["rscale"];
    
  // Step in the path
  $step=1;
  if (isset($_REQUEST["path_step"]))
    $step=$_REQUEST["path_step"]+1;
  // Add step
  $onclick.="&path_step=$step";
  
  // Add selected layers
  foreach ($_REQUEST as $key => &$value) {
    if ($value != "on" ) continue;
    $onclick.="&$key=on";
  }
  
  //Fullscreen option 
  $onclick.="&fs='+$('#fs').val()+'";
  
  // Add tid and cur_point if there
  if (isset($_REQUEST['tid']) )
    $onclick.="&tid=".$_REQUEST["tid"];
    
  // Routing category
  if (isset($_REQUEST['routecat']) )
    $onclick.="&routecat=".$_REQUEST["routecat"];
  
  // Do not take it from REQUEST...
  // cause the path plotting function 
  // changes it
  if ($cur_poi){
    $tmp_poi=$cur_poi;
    
    if (isset($vr["poi_in_floor"])) 
      $tmp_poi+=$vr["poi_in_floor"];
      
    $onclick.="&cur_poi=$tmp_poi";
  }
  
  // SVG! New
  if (isset($_REQUEST['svg'])) $onclick.="&svg=".$_REQUEST['svg'];
    
  // Close JS
  $onclick.="'";
  
  return $onclick;
}

/**
 * Linking function between mymapslib and libgps.
 * It creates/inits all the data req
 *
 */
function getFloorGPSData($id){
  $f = new floors($id);
  // Create data!
  $data = array(
    "g1" => array("lat_r" => $f->get_orig_lat_r(), "lon_r" => $f->get_orig_lon_r(),
		  "lat" => rad2deg($f->get_orig_lat_r()), "lon" => rad2deg($f->get_orig_lon_r())
		  ),
		  
    "p1" => array("x" => $f->get_orig_x(), "y" => $f->get_orig_y()),
    
    "stats" => array(
		      "rot_r" => $f->get_rotation_r(),
		      "rot" => rad2deg($f->get_rotation_r()),
		      "PPD" => array("x" => $f->get_ppd_x(), "y"=> $f->get_ppd_y())
		    )
  );
  
  return $data;
}


/**
 * Map to overiview based on PIXELS. 
 * If the offsets are not there try GPS 
 */
function getOverViewPos($loc, $checkFloorPin=FALSE, $ov=NULL){
  global $FLOOR_OUTDOOR_PIN;
  $latLon = array();
  
  $ftmp = new floors($loc->bf);
  
  // if the floor does not support overview pins... skip
  if ($checkFloorPin && ($ftmp->get_type()&$FLOOR_OUTDOOR_PIN)!=$FLOOR_OUTDOOR_PIN) return FALSE;
  
  $lat_s = $ftmp->get_lat_scale_factor();
  $lat_o = $ftmp->get_lat_offset_factor();
  $lon_s = $ftmp->get_lon_scale_factor();
  $lon_o = $ftmp->get_lon_offset_factor();
 
  // If the offsets are not there try GPS 
  if ($lat_s == 0 || $lon_s == 0) 
    return getOverViewPosGPS($loc, $checkFloorPin, $ov);

  $latlon["lat"] = $loc->lat * $lat_s + $lat_o;
  $latlon["lon"] = $loc->lon * $lon_s + $lon_o;

  return $latlon;
}

function getOverViewPosXY($x,$y, $bf){
  $fake = new locations();
  $fake->lat=$x;
  $fake->lon=$y;
  $fake->bf=$bf;
  return getOverViewPos($fake);
}

/**
 * Map to overiview based on GPS
 * 
 * Allow overview to be given (avoid selects in loops)
 */
function getOverViewPosGPS($loc, $checkFloorPin=FALSE, $ov=NULL){

  global $FLOOR_OUTDOOR_PIN;
  $latLon = array();
  
  $ftmp = new floors($loc->bf);

  // if the floor does not support overview pins... skip
  if ($checkFloorPin && ($ftmp->get_type()&$FLOOR_OUTDOOR_PIN)!=$FLOOR_OUTDOOR_PIN) return FALSE;
  


  
  // Now make floor data 
  $loc_data = getFloorGPSData($loc->bf);
  
  // Check for luck of data
  if ($loc_data["p1"]["orig_x"]==null || $loc_data["p1"]["orig_x"]==-1) return FALSE;
  
  // Map floor=>GPS
  $gps = Map2GPS($loc->lat, $loc->lon, $loc_data);
  
  // Load overview
  if ($ov==NULL) $ov = getOverviewFloor();
  
  // Get ov data
  $ov_data = getFloorGPSData($ov->id);
  
  $ov_pix = GPS2Map($gps["y"], $gps["x"], $ov_data);
  
  $latlon["lat"] = $ov_pix["x"];
  $latlon["lon"] = $ov_pix["y"];
  
  return $latlon;
}


#
# Get a float for the current foor
#
function floor2float($floor){

  // All lower case
  $floor = strtolower($floor);
  
  // Catch basic names here
  if ($floor=="ground" || $floor=="G") return 0;
  else if ($floor=="overview") return 0;
  else if ($floor=="basement" || $floor=="LG") return -1;
  else if ($floor=="a") return 0;
  else if ($floor=="b") return 1;
  else if ($floor=="c") return 2;
  
  // Warwick! L01,L02 (level)
  while (!is_numeric($floor[0])){
    $floor=substr($floor,1);
  }

  
// echo "<!--Floor: $floor -->";
  // Try to get an integer...
  $val = intval($floor);
// echo "<!--Val: $val -->";  
  

  

  
  // Check for a,b,c after floor number!
  if ($val>0 && strpos($floor,"a")!==FALSE) $val+=0.2;
  if ($val>0 && strpos($floor,"b")!==FALSE) $val+=0.4;
  if ($val>0 && strpos($floor,"c")!==FALSE) $val+=0.6;
  
  return $val;
}

#
# Select the proper pin for indoors navigation
#
function getIndoorPin($next_building, $next_floor){
  global $mp_settings, $fconf, $config;
  
  // Default
  $pin = $mp_settings->get_next_pin();
  // If moving from overview -> inside return normal pin
  if (isOverview() || $next_building=="0") return $pin;
  $toval = floor2float($next_floor);
  $fromval = floor2float($fconf->floor);

  
  // same floor... do nothing
  if ($fromval==$toval) return $pin;
  
  $base="down";
  if ($toval>$fromval) $base="up";
  
 $base.=strtolower($next_floor).".png";
//  $base.=strtolower($toval).".png";  //KG changed 14/10/2013
 
  $pin = $base;

  // Check that exists...
  if (!is_file("projects/$config/images/$pin")) return $mp_settings->get_next_pin();

  // return the pin
  return $pin;
  
}

#
# Create a decent message for the pins (Enter/Exit, Proceed to etc)
#
function getPinMessage($next_building, $next_floor, $back_proceed="Proceed"){
  global $mp_settings, $fconf;
  
  $msg = "";
  
  // If moving from overview -> inside 
  if (isOverview()) 
    return "输入你所在楼层".$next_floor.")";
  
  // If moving from inside -> overview
  else if ($next_building=="0")
    return "退出该楼";
    
  $msg=$back_proceed." 前往 ".$next_floor;
  
  return $msg;
  
}

function getReportProbURLParams(){
  global $g_mapState;
  
  $from = $to =$searchparam ="";
  if (isset($_REQUEST["room"]) && $_REQUEST["room"]!=""){
    $to=$_REQUEST["room"];
  }
  
  // Get source OR SEARCH!!
  if ($g_mapState->from!=""){
    $from=$g_mapState->from;
  }else if (isset($_REQUEST["search"]) && $_REQUEST["search"]!=""){
    $from=$_REQUEST["search"];
  }
  
  if (isset($_REQUEST["st_elev_gr"]) && $_REQUEST["st_elev_gr"]=="stairs"){
    $stairs="stairs=";
  }
  if (isset($_REQUEST["yah_x"]) && $_REQUEST["yah_x"]!=""){
		$searchparam="&yah_x=".$_REQUEST["yah_x"]."&yah_y=".$_REQUEST["yah_y"]."&bf=".$_REQUEST["bf"];
  }
  $params="from=".urlencode($from)."&to=".urlencode($to)."&searchparam=".urlencode($searchparam); //KG removed - don't report."&$stairs";
  
  
  return $params;
}

/**
 * Get the content of the popup window that will be displayed 
 * on the onclick function of the marker. This is constructed
 * based on the arguments rather than a location, for more
 * flexibility...
 * 
 * $directions from estimated... removes some options and is used on overview
 * destinations estimation and other app that do not support directions to 
 * a point...
 */
function getGenericMarkerMessage($loc, $desc="", $more_info="", $catname="", $directions=TRUE, $twoStage=FALSE) {
  global $CAT_TYPE_NOAUTO,$CAT_TYPE_DISPLAYTEXT;
  
  // Check for theme-able message and call it if there
  if ( function_exists("getGenericMarkerMessageTheme")){
    return getGenericMarkerMessageTheme($loc,$desc,$more_info,$catname,$directions,$twoStage);
  } 
  
  // Get display name. IF there is a search field
  // use the user typed name (which may be an alias!)
  $name = $loc->get_name();
  if ($loc->searched_as) $name = $loc->searched_as;
  
  // Code to close the popup from an internal link!
  $closecode="findNclose(this);";
  
  // Replace LXXXX (where XXXX is numeric) with the category name rather than the location name 
  // Do it category based...
  $cat = getCatFromName($catname);
    $newway = (
    (($cat->type&$CAT_TYPE_NOAUTO) == $CAT_TYPE_NOAUTO || ($cat->type&$CAT_TYPE_DISPLAYTEXT) == $CAT_TYPE_DISPLAYTEXT)  
    && $catname!="");
  
  if ($newway) $notes="<h1>".$catname."</h1>"; // Use the catname instead of location
  else         $notes="<h1>".$name."</h1>";    // Use the locations' name
 
  if ($desc!="") $notes.="<h2><i>".$desc."</i></h2>";
  else if (!$newway && !isRootCat($cat)) $notes.="<h2><i>".$cat->description."</i></h2>";

  $notes.="<div class=\"center\">";
  
  // Convert the name to appropriate format...
  // double escape quotes!!
  // Example:
  // name = "Student's"
  // setAs(\'name\')
  // so the 's in the name has to be \\\'!!!
  $namesafe=addslashes(addslashes(DB2name($name)));
  if ($directions){
    $notes.="<input type=\"button\" class=\"msg_setdst\" value=\"作为目的地\" onmousedown=\"setAsDestination(\\'$namesafe\\',$loc->id);$closecode\"  ontouchend=\"setAsDestination(\\'$name\\',$loc->id);$closecode\"/>";
    $notes.="<input type=\"button\" class=\"msg_setsrc\" value=\"作为出发地 \" onmousedown=\"setAsSource(\\'$namesafe\\',$loc->id);$closecode\" ontouchend=\"setAsSource(\\'$name\\',$loc->id);$closecode\"/>";
  }
  // Share
  $notes.="<input type=\"button\" class=\"msg_email\" value=\"分享该地点\" onmousedown=\"sendLoc(\\'".rawurlencode($namesafe)."\\',$loc->id,this);$closecode\" ontouchend=\"sendLoc(\\'".rawurlencode($namesafe)."\\',$loc->id,this);$closecode\"/>";
  // Report Problem
  $notes.="<input type=\"button\" class=\"msg_report\" value=\"报告问题\" onmousedown=\"reportProb(\\'".getReportProbURLParams()."\\');$closecode\" ontouchend=\"reportProb(\\'".getReportProbURLParams()."\\');$closecode\"/>";
  // More Info
  if ($more_info!="") $notes.="<input type=\"button\" class=\"msg_info\" value=\"更多信息\" onmousedown=\"showMoreInfo(\\'$more_info\\');\" ontouchend=\"showMoreInfo(\\'$more_info\\');\"/>";
  
  $set_info = "lname=" . urlencode($loc->get_name());
  $set_info.= "&lid=" . $loc->id;
  $notes.="<input type=\"button\" class=\"msg_info\" value=\"设置信息\" onmousedown=\"setMoreInfo(\\'$set_info\\');\" ontouchend=\"showMoreInfo(\\'$more_info\\');\"/>";
  
  // Go inside room
  $fplan = getFloorPlanForRoom($loc);
  $fplan2 = getFloorPlanForBuilding($loc);
  if ($fplan!==FALSE){
    $notes.="<input type=\"button\" class=\"msg_inside\" value=\"进入里面\" onmousedown=\"goInside(".$fplan->id.");\" ontouchend=\"goInside(".$fplan->id.");\"/>";
  }
  // Go inside building
  else if ($fplan2!==FALSE){
    $notes.="<input type=\"button\" class=\"msg_inside\" value=\"Go Inside...\" onmousedown=\"goInside(".$fplan2->id.");\" ontouchend=\"goInside(".$fplan2->id.");\"/>";
  }
  else if ($twoStage!==FALSE){
    $notes.="<input type=\"button\" class=\"msg_inside\" value=\"进入里面\" onmousedown=\"goInside(".$loc->bf.",true);\" ontouchend=\"goInside(".$loc->bf.",true);\"/>";
  }
  
//   if (isset($_REQUEST['a']) && $_REQUEST['a']="psou"){
//     $notes.="<input type=\"button\" class=\"msg_inside\" value=\"Edit\" onmousedown=\"tmp_doEdit('".$loc->name."');\"/>";
//   }
  
  $notes.="</div>";
  
  return $notes;
}

function getBuildingMarkerMessage($name,$clocs) {
  // Check for theme-able message and call it if there
  if ( function_exists("getBuildingMarkerMessageTheme")){
    return getBuildingMarkerMessageTheme();
  } 
  
  // Code to close the popup from an internal link!
  
  $closecode="findNclose(this);";
  
  $notes="<h1>$name</h1>";
  $notes.="<div class=\"center\">";
  
  foreach($clocs as $key=>$value){
  // Share
	$count=0;
	$vs=array();
	foreach($value as $v){
		$o=array();
		$o[0]=$v->bf;
		$o[1]=$v->fl;
		$o[2]=$v->sum;
		$count+=$v->sum;
		array_push($vs,$o);
	}
	$json=json_encode($vs);
	$json=str_replace("\"","", "$json");
	//$notes.="<div class=\"msg_inside\" onmouseover=\"showList(\'$json\',this)\" >$key  $count</div>";
	$notes.="<input type=\"button\" class=\"msg_inside\" value=\"$key  $count\" onmouseover=\"showList(\'$json\',this)\"/>";
  }
  $notes.="</div>";
  
  return $notes;
}

function getStartMarkerMessage() {
  
  // Check for theme-able message and call it if there
  if ( function_exists("getStartMarkerMessageTheme")){
    return getStartMarkerMessageTheme();
  } 
  
  
  // Code to close the popup from an internal link!
  
  $closecode="findNclose(this);";
  
  $notes="<h1>开始地点</h1>";
  $notes.="<div class=\"center\">";
  
  // Share
  $notes.="<input type=\"button\" class=\"msg_email\" value=\"分享路径\" onmousedown=\"sendLoc(null,null,this);\"  ontouchend=\"sendLoc(null,null,this);\"/>";
  $notes.="<input type=\"button\" class=\"msg_email\" value=\"分享该地点\" onclick=\"sendLoc(\\'$namesafe\\',$loc->id);$closecode\" ontouchend=\"sendLoc(\\'$name\\',$loc->id);$closecode\"/>";
  // Report Problem
  $notes.="<input type=\"button\" class=\"msg_report\" value=\"报告问题\" onclick=\"reportProb(\\'".getReportProbURLParams()."\\');$closecode\" ontouchend=\"reportProb(\\'".getReportProbURLParams()."\\');$closecode\"/>";
  // More Info
  if ($more_info!="") $notes.="<input type=\"button\" class=\"msg_info\" value=\"更多信息\" onclick=\"showMoreInfo(\\'$more_info\\');\" ontouchend=\"showMoreInfo(\\'$more_info\\');\"/>";
  
  $notes.="</div>";
  
  return $notes;
}



function getMultiMarkerMessage($loc, $desc="", $more_info="", $catname="", $directions=TRUE) {
  global $CAT_TYPE_NOAUTO;
  
  $name = $loc->get_name();
  /// Make notes message (window content)
  $closecode="findNclose(this);";
  
  // Replace LXXXX (where XXXX is numeric) with the category name rather than the location name 
  // Do it category based...
  $cat = getCatFromName($catname);
  //$oldway = (substr($name,0,1) == "L" && is_numeric(substr($name,1,1)) && $catname!="");
  //$newway = ((($cat->type&$CAT_TYPE_NOAUTO) == $CAT_TYPE_NOAUTO) && $catname!="");
  
  //if ($newway) $notes="<h1>".$catname."</h1>"; // Use the catname instead of location
  //else         $notes="<h1>".$name."</h1>";    // Use the locations' name
 
  if ($desc!="") $notes.="<h2><i>".$cat->description."</i></h2>";

  $notes.="<div class=\"center\">";
  
  // Convert the name to appropriate format...
  // double escape quotes!!
  // Example:
  // name = "Student's"
  // setAs(\'name\')
  // so the 's in the name has to be \\\'!!!
  $namesafe=addslashes(addslashes(DB2name($name)));
  if ($directions){
    $notes.="<input type=\"button\" class=\"msg_setdst\" value=\"作为目的地\" onclick=\"setAsDestination(\\'$namesafe\\',$loc->id);$closecode\"  ontouchend=\"setAsDestination(\\'$name\\',$loc->id);$closecode\"/>";
    $notes.="<input type=\"button\" class=\"msg_setsrc\" value=\"作为出发地\" onclick=\"setAsSource(\\'$namesafe\\',$loc->id);$closecode\" ontouchend=\"setAsSource(\\'$name\\',$loc->id);$closecode\"/>";
  }
  // Share
  $notes.="<input type=\"button\" class=\"msg_email\" value=\"分享该地点\" onclick=\"sendLoc(\\'$namesafe\\',$loc->id);$closecode\" ontouchend=\"sendLoc(\\'$name\\',$loc->id);$closecode\"/>";
  // Report Problem
  $notes.="<input type=\"button\" class=\"msg_report\" value=\"报告问题\" onclick=\"reportProb(\\'".getReportProbURLParams()."\\');$closecode\" ontouchend=\"reportProb(\\'".getReportProbURLParams()."\\');$closecode\"/>";
  // More Info
  if ($more_info!="") $notes.="<input type=\"button\" class=\"msg_info\" value=\"更多信息\" onclick=\"showMoreInfo(\\'$more_info\\');\" ontouchend=\"showMoreInfo(\\'$more_info\\');\"/>";
  
  // Go inside room
  $fplan = getFloorPlanForRoom($loc);
  $fplan2 = getFloorPlanForBuilding($loc);
  if ($fplan!==FALSE){
    $notes.="<input type=\"button\" class=\"msg_inside\" value=\"进入里面\" onmousedown=\"goInside(".$fplan->id.");\" ontouchend=\"goInside(".$fplan->id.");\"/>";
  }
  // Go inside building
  else if ($fplan2!==FALSE){
    $notes.="<input type=\"button\" class=\"msg_inside\" value=\"进入里面\" onmousedown=\"goInside(".$fplan2->id.");\" ontouchend=\"goInside(".$fplan2->id.");\"/>";
  }
  
  $notes.="</div><h3></h3>";
  
  return $notes;
}


#
# Draw the YAH and/or Prev marker...
#
function drawYAHandPrev($vr, $yah=true, $pre=true){
  global $mp_settings,$fconf;

  // YAH
  if ($yah){
    // It is a search so YAH and End marker fall on top of each other... do nothing
    if ((!isset($_REQUEST['room']) || $_REQUEST['room']=="") &&    // No room or destination!  AND
	( 
	 (isset($_REQUEST['from']) && $_REQUEST['from'] != "") || // From is set OR
	 (isset($_REQUEST['search']) && $_REQUEST['search']!= "" ) // Search is set
	) 
       )
      echo ""; 
      
    // Overview, default starting point (OR Double Click) 
    else if ($mp_settings->default_bf == $fconf->id || !isset($_REQUEST['path_step']) || $_REQUEST['path_step']==0) {
      drawYouAreHere($mp_settings->yah_x,$mp_settings->yah_y);
      // FIXME: move to set center (php)
      if ($mp_settings->yah_x!="" && $mp_settings->yah_y!="")
	echo "map.setCenter(new Point($mp_settings->yah_x,$mp_settings->yah_y));";
    }
    
  }
  
  
  // Previous marker. TODO: mymapslib function in the future... (split maybe?)
  if ($pre){
    if (isset($_REQUEST["yah_node_id"]) && !isset($_REQUEST["yah_x"])){
      $onclick="javascript:history.go(-1)";
      $tmp_v = new vertex($_REQUEST["yah_node_id"]);
      drawMarker($tmp_v->lat,$tmp_v->lon,
		 "Go Back","",
		 $mp_settings->get_prev_pin(), "false", "fplan",$onclick);
    }
  }
}



/**
 * Draw a location on the current floor plan. If the final location is on
 * a different floor plan, this function will add the "Next" marker.
 * 
 * @param loc The location object
 * @param popup The message that appears on mouse over (to the added marker)
 * @param more_info This locations more information
 * @param vr The result of routing to this location (Vertical Routing info)
 * @param add_message A boolean to indicate if a "balloon" message should appear 
 *                    when the user clicks on the marker...
 * @param open_message A boolean to indicate if the message should be opened or not
 *                     when the page is first displayed...
 */
function drawLocation($loc, $popup, $more_info, $vr, $add_message=FALSE, $open_message="false"){
  global $mp_settings, $fconf;
  
  $tmp_lat=-100;
  // Add marker to location if we are on the proper floor

  if ($fconf->id == $loc->bf /*&& !$vr["used"]*/){
    // If we display the marker it self then consult $add_message
    // to see weather we want a marker message or not...
    $message="";
    $tmp_cat = new categories($loc->get_cid());
    if ($add_message) $message=getGenericMarkerMessage($loc, $loc->get_description(), $more_info, $tmp_cat->get_name());
    drawMarker($loc->get_lat(),$loc->get_lon(), $popup, $more_info,$mp_settings->get_end_pin(), "false", "location","",$message, $open_message);
    echo locationHighLightCode($loc);
    $tmp_lat=$loc->get_lat()/1;
    $tmp_lon=$loc->get_lon()/1;
  }
  

  if ($vr["used"]){
    // Get onclick
    $onclick=getOnClickLocLink($loc, $vr);
    
    
    drawMarker($vr["last_x"],$vr["last_y"],
		getPinMessage($vr["next_b"],$vr["next_f"]),"",
		getIndoorPin($vr['next_b'], $vr['next_f']), "false", "fplan",$onclick);
  }


  // last 2 cases together
  // Draw where the location is (more or less)
  // ONLY ON OVERVIEW!
  $r = getOverViewPos($loc, TRUE);
  if ($r !== FALSE && isOverview()){
    $tmp_cat = new categories($loc->get_cid());
    if ($add_message) $message=getGenericMarkerMessage($loc, $loc->get_description(), $more_info, $tmp_cat->get_name(), TRUE);
    drawMarker($r["lat"],$r["lon"], "最终目标", "", $mp_settings->location_pin, "false", "est_location","",$message, $open_message);
  }
        
  // Set Proper center (TODO: FIXME: Make it a separate function)
  if (/*!isOverview() &&*/ $mp_settings->yah_x) 
    echo "map.setCenter(new Point(".($mp_settings->yah_x).",".($mp_settings->yah_y)."));"; // kg - test 23/12/2011
  else if ($tmp_lat!=-100)
    echo "map.setCenter(new Point(".($tmp_lat).",".($tmp_lon)."));";
 
}

function isTransparentPin($poly,$image){
  if ($poly!="" && preg_match("/blank/" , $image)>0) return TRUE;
  return FALSE;
}

/**
 * ?
 *
 */
function drawSearchLocation($loc, $popup, $more_info, $add_message=FALSE, $open_message="false", $directions=TRUE){
  global $mp_settings, $fconf;
  
  if (!$loc || $loc->_new) return;
  
  $tmp_lat = $loc->get_lat();
  $tmp_lon = $loc->get_lon();
  $tmp_poly = $loc->polygon;
  
  $twoStage = is2StageSearch($loc);
  
  if ($twoStage){
    $tmp_lat = $twoStage["lat"];
    $tmp_lon = $twoStage["lon"];
  }
  
  // More Info
  $ff = new floors($loc->bf);
  $infoname=$ff->building . "_" . $ff->floor . "_" . $loc->name;
  if (hasMoreInfo($infoname, $FS_PRE)){
    $more_info=getMoreInfoWithMeta($loc, $FS_PRE);
  }
  
  // If we display the marker it self then consult $add_message
  // to see whether we want a marker message or not...
  $message="";
  if ($add_message) {
    $tmp_cat = new categories($loc->get_cid());
    $message=getGenericMarkerMessage($loc, $loc->get_description(), $more_info,$tmp_cat->get_name(),$directions,$twoStage);
  }
  
  
  // Show locations' highlight
  if ($twoStage===FALSE) {
    // If a marker wont be displayed...
    $open="false";
    if (isTransparentPin($loc->get_polygon(), $mp_settings->get_search_pin()))
     $open="true";
     
//     echo locationHighLightCode($loc,$message,$open);
    echo locationHighLightCode($loc);
    // Add marker and highlight
    drawMarker($tmp_lat,$tmp_lon, $popup, $more_info,$mp_settings->get_search_pin(),"false","","",$message,$open_message,$tmp_poly);
    
  // 2-Stage Search!
  }else{
    $tmpf = new floors($loc->bf);
    $fname = $tmpf->get_description();
    $fname = explode(",", $fname);
    $fname = $fname[0];
    $ov_loc = getLocFromName($fname);

    
    // Show buildings' highlight
    if ($ov_loc && !$ov_loc->_new) {
      // clickable polygon / no image overlay
      // If a marker wont be displayed...
//       $open="false";
//       if (isTransparentPin($ov_loc->get_polygon(), $mp_settings->get_search_pin()))
// 	$open="true";
// 	
//       echo locationHighLightCode($ov_loc,$message,$open);

      echo locationHighLightCode($ov_loc);
    }
    
    
    // Add marker and highlight
    drawMarker($tmp_lat,$tmp_lon, $popup, $more_info,$mp_settings->get_search_pin(),"false","","",$message,$open_message,$ov_loc->get_polygon());
  }
    
  $tmp_lat/=1;
  $tmp_lon/=1;
  
  echo "map.setCenter(new Point(".($tmp_lat).",".($tmp_lon)."));";
 
}


function locationHighLightCode($loc, $message="",$open_message="false"){
    global $PIXEL_MODE, $fconf, $mp_settings;
    $mode="";
    if ($fconf->mode == $PIXEL_MODE)
      $mode="Px";
    
    // *NEW: If we have a poly ... add it
    $poly =$loc->polygon;
    if ($poly=="") return "";
    
    // !! we have!
    $parts = explode("\n", $poly);
    
    $marker_js= "map.addPoly([\n";
    // for each row!
    for ($row=0; $row<count($parts)-1; $row++){
    
      // get x,y
      $p2 = explode(",",$parts[$row]);
      $p_x=$p2[0]/1;
      $p_y=$p2[1]/1;
      
      // Check mode
      if ($row==0) $comma="";
      else 	 $comma=",";
      if ($mode == "")
	$marker_js.="$comma\nnew LatLon($p_x, $p_y)";
      else
	$marker_js.="$comma\nnew Point($p_x, $p_y)";
    }
    
    // Create proper message for the polygon...
    if ($message=="")
      $message="null";
    else
      $message="'$message'";
      
    // Now Get Location Highlight colors from the DB
    $pstyle = $mp_settings->get_pstyle();
    if (isOverview()){
      $pstyle = $mp_settings->get_outpstyle();
    }
    
    $parts = explode(";", $pstyle);
      
    $marker_js.="],{'cursor':'hand','stroke-width': 3, 'stroke-opacity': 1, 'stroke': '".$parts[0]."', 'stroke-dasharray': '..', 'fill': '".$parts[1]."', 'fill-opacity': ".$parts[2]."}, $message,'loc_highlight',$open_message);";

	
    return $marker_js;
}

function drawYouAreHere($lat,$lon, $popup="", $more_info=""){
  global $mp_settings, $config, $g_mapState;
  $info=getimagesize("projects/$config/images/".$mp_settings->get_yah_image());
  $img_w=$info[0];
  $img_h=$info[1];
  $msg="";
  if ($g_mapState->isRouting() || $g_mapState->isCatRouting())
    $msg=getStartMarkerMessage();
  drawMarker($lat,$lon, $popup, $more_info, $mp_settings->get_yah_image(), "false", "yah","",$msg);
}

/**
 * Add marker as location by default. Option to Override the image
 * is also provided. The default pin is the "search_pin" from the `settings` 
 * table. This function will _just_ draw the marker and it will _not_ handle 
 * any centering (cause it is used from layer points too).
 * 
 * Most of the markers should call this function instead of re-implementing 
 * the following code.
 * 
 * Multiple 'onclick' functionality is provided by this function. For simplicity
 * on the user side, only ONE on click action will be selected using the foloowing
 * priorities:
 * 
 * 1. The $message will cancel all other
 * 2. The more_info over-rides the onclick
 * 3. The onclick is applied only if the above are ""
 * 
 * @param lat The latitude or X of the marker
 * @param lon The longitude or Y of the marker
 * @param popup The message to appear on mouse over
 * @param more_info The extra information that this marker has
 * @param image The image to be used for drawing the marker (this is used from "Next", "Prev", etc)
 * @param scale Scale the marker or not (scale=1)
 * @param lname The JS layer name to be used on the marker (layers can be hidden dynamically - not implemented)
 * @param onclick The onclick function of the marker (custom)
 * @param message The context of the "balloon" message that appears when the user clicks on the marker...
 * 
 * 
 */
function drawMarker($lat,$lon, $popup, $more_info, 
		    $image="", $scale="false", $lname="", $onclick="", 
		    $message="", $show_message="false", $poly=array()){
  global $mp_settings, $fconf,$GPS_MODE,$PIXEL_MODE, $config, $FS_PRE;

  
  // Add the project path to the image
  $img="${FS_PRE}projects/$config/images/".$image;
  if ($image == "" || !file_exists($img)) {
    $img="${FS_PRE}projects/$config/images/".$mp_settings->get_end_pin();
    $lname="location";
  }
  
  $image=$img;
  

  // GPS
  if ($lat==0 || $lon==0) return;
  // PIXEL
  if ($lat==-1 || $lon==-1) return;
  
  $lat/=1;
  $lon/=1;
  $info=getimagesize($image);
  $img_w=$info[0];
  $img_h=$info[1];
  $img_pin_x = $img_w/2;
  $img_pin_y = $img_h;
//   $scale = "true";
  
  // check for blank marker 
  if ($poly!="" && preg_match("/blank/" , $image)>0){
    $box = getBoundingBox($poly);
    $img_w=$box["max"]["x"] - $box["min"]["x"];
    $img_h=$box["max"]["y"] - $box["min"]["y"];
    $img_pin_x = $img_w/2;
    $img_pin_y = $img_h/2;
    $scale="true";
  }
  $marker_js="
  var marker = {
      'layerName' : '$lname',
      'icon' : '$image',
      'icon_width' : $img_w,
      'icon_height' : $img_h,
      'icon_scale' : $scale,
      'pin_point' : {'x':$img_pin_x, 'y':$img_pin_y},
      'popup' : '$popup'
  ";
  // Check and add a balloon message on the on click function
  // if this is provided in the "message" variable

  
  $message=replaceSQouteForJSON($message);
  
  if ($message!=""){
   $marker_js.=",'message':'$message'";
   $marker_js.=",'show_message':$show_message";
  }
  
  // Check scaling (if missing is assumed true from JS)

  if ($scale=="false")
    $marker_js.=",'nozoomscale': 1";
 
  $marker_js.="};";
  
  // Handle onclick options based on their priority
  if ($message == ""){
    if ($more_info!="")
      $marker_js .="\n	marker.onclick=function(){showMoreInfo(\"$more_info\");}";
    else if ( $onclick!= "" )
      $marker_js .="\n	marker.onclick=function(){\n\t $onclick\n\t}";
  }
  
  if ($fconf->mode == $GPS_MODE){
    $marker_js .= "
      map.addMarker( marker, new LatLon($lat,$lon));
    ";
  }else if ($fconf->mode == $PIXEL_MODE){
    $marker_js .= "
      map.addMarker( marker, new Point($lat,$lon));
    ";
  }

  // Do not compressJS here... screws up
  echo $marker_js."\n";
}



#
# Draw all the points of the layer with name "$layer"
#
function drawLayer($layer){
  global $connection, $error, $fconf,$GPS_MODE,$PIXEL_MODE,$config,$FS_PRE;

  $l = getLayerFromName($layer);

  // Get layer Image
  $limg = "$FS_PRE/projects/$config/images/".$l->get_image_name();
  $last = $limg[strlen($limg)-1];
  if ($limg == "" | !file_exists ($limg) | $last=="/"){
    $limg="$FS_PRE/projects/$config/images/layers/blank.png";
    $limg_w=1;
    $limg_h=1;
  }else{
    $info=getimagesize($limg);
    $limg_w=$info[0];
    $limg_h=$info[1];
  }

  // Sanity check
  if (!$connection->isConnected()) {
    die('无法连接: ' . $connection->last_error);
    $error='无法连接: ' . $connection->last_error;
  }

  $lps = getLayerPointsFrom($l->id, $fconf);

 foreach ($lps as $lp){

    $lat=$lp->get_lat()/1;
    $lon=$lp->get_lon()/1;
   
    if ($lp->get_lp_image() != "") {
      $img="$FS_PRE/projects/$config/images/".$lp->get_lp_image();
      $info=getimagesize($img);
      $img_w=$info[0];
      $img_h=$info[1];
    }else {
      $img=$limg;
      $img_w=$limg_w;
      $img_h=$limg_h;
    }
  
    $name=$lp->get_name();
    $notes=$lp->get_notes();
    $marker_js="
    var marker = {
	'layerName' : '$layer',
	'icon' : '$img',
	'icon_width' : $img_w,
	'icon_height' : $img_h,
	'pin_point' : {'x':$img_w/2, 'y':$img_h/2},
	'popup' : \"$name\"
    ";
    /**
     * If there is a file with this name, show the file as an iframe,
     * else use the note (if any) for a popup div message.
     */
    $more_info="";
    // info by layer name...
    if (hasMoreInfo($lp->id, $FS_PRE,"layers")){
      $more_info = getMoreInfoWithHead($lp->id, $FS_PRE,"layers");
      // We need to decode cause it is not on a browser element so it is not 
      // interpreted by the browser... JS will not replace &gt; and &lt; to tags...
      // Hint: see "function(){ ... }" below
      $more_info = html_entity_decode($more_info);
    }else if ($notes!="")
      $marker_js.="	,'message':'$notes'";

    // Finalize marker
    $marker_js.="};";
    if ($more_info!="") $marker_js .="marker.onclick=function(){showMoreInfo('$more_info');};";
    
    // Add it on the map
    if ($fconf->mode == $GPS_MODE){
      echo "
	$marker_js
	map.addMarker( marker, new LatLon($lat,$lon));
      ";
    }else if ($fconf->mode == $PIXEL_MODE){
      echo "
	$marker_js
	map.addMarker( marker, new Point($lat,$lon));
      ";
    }
    
  }
}

function getLocTransPoly($l){
  $poly = $l->get_polygon();
  
  if ($poly=="") return "";
  // !! we have!
  $parts = explode("\n", $poly);

  $marker_js= "map.addPoly([\n";

  // for each row!
  for ($row=0; $row<count($parts)-1; $row++){

    // get x,y
    $p2 = explode(",",$parts[$row]);
    $p_x=$p2[0]/1;
    $p_y=$p2[1]/1;
    
    // Check mode
    if ($row==0) $comma="";
    else 	 $comma=",";
    
    $marker_js.="$comma\nnew Point($p_x, $p_y)";
  }

    
  // Now Get Location Highlight colors from the DB
  $pstyle = "none;white;0";

  $parts = explode(";", $pstyle);
  
  $more_info="";
  if (hasMoreInfo($l->id, $FS_PRE)){
    $more_info=getMoreInfoWithMeta($l, $FS_PRE);
  }
    
  $notes="";
  $notes=getGenericMarkerMessage($l, $l->get_description(), $more_info);
    
  $marker_js.="],{'cursor':'hand', 'stroke-width': 3, 'stroke-opacity': 1, 'stroke': '".$parts[0]."', 'stroke-dasharray': '..', 'fill': '".$parts[1]."', 'fill-opacity': ".$parts[2]."}, '$notes','hideonmove');";
  
  return $marker_js;
}

#
# Draw all the locations of the category with name "$cat"
# as layer points!!
#
function drawCatLayer($cat, $add_message=TRUE, $directions=TRUE){
  global $error, $fconf,$GPS_MODE,$PIXEL_MODE,$config,$FS_PRE;

  $c = getCatFromName($cat);
  
  // Check that it exists
  if ($c->_new){
    echo "$cat Does not exists";
    return;
  }
  
  // Fake layer name
  $layer = $c->get_name();
  

  // Get category Image
  $cimg = "$FS_PRE/projects/$config/images/".$c->get_image_name();
  $last = $cimg[strlen($cimg)-1];

  // Keep cimg always pointing correct
  $img_tmp = "";
  if (($cimg == "" || !file_exists ($cimg) || $last=="/")){
    $img_tmp = $cimg;
    $cimg="$FS_PRE/projects/$config/images/layers/blank.png";
    $cimg_w=1;
    $cimg_h=1;
  }else{
    $info=getimagesize($cimg);
    $cimg_w=$info[0];
    $cimg_h=$info[1];
    $img_tmp = $c->get_image_name();
  }


  $locs = getAllLocsForCategory($c->id, $fconf);  

 foreach ($locs as $l){

    $lat=$l->get_lat()/1;
    $lon=$l->get_lon()/1;
  
    $name=$l->get_name();
    $cname=$c->get_name();
	
    /**
     * If there is a file with this name, show the file as an iframe,
     * else use the note (if any) for a popup div message.
     */
    $more_info="";
    if (hasMoreInfo($l->id, $FS_PRE)){
      $more_info=getMoreInfoWithMeta($l, $FS_PRE);
    }
     
    $notes="";
    if ($add_message) {
	$notes=getGenericMarkerMessage($l, $l->get_description(), $more_info, $cname, $directions);
    }
    
    
    $poly = $l->get_polygon();
    // check for blank marker 
    if (preg_match("/blank2/" , $img_tmp)>0){
      echo getLocTransPoly($l);
      continue;
    }
    
      // check for blank marker 
    if ($poly!="" && preg_match("/blank/" , $img_tmp)>0){
      $box = getBoundingBox($poly);
      $cimg_w=$box["max"]["x"] - $box["min"]["x"];
      $cimg_h=$box["max"]["y"] - $box["min"]["y"];
//       $notes="";
    }
    
   
    $marker_js="
    var marker = {
	'layerName' : '$layer',
	'icon' : '$cimg',
	'icon_width' : $cimg_w,
	'icon_height' : $cimg_h,
	'pin_point' : {'x':$cimg_w/2, 'y':$cimg_h/2},
	'popup' : \"$name\"
    ";

    // ESCAPE for JSON
    $notes = replaceSQouteForJSON($notes);
    
    if ($notes!="")
      $marker_js.="	,'message': '$notes'";

    // Finalize marker
    $marker_js.="};";
    
    
    // Add it on the map
    if ($fconf->mode == $GPS_MODE){
      echo "
	$marker_js
	map.addMarker( marker, new LatLon($lat,$lon));
      ";
    }else if ($fconf->mode == $PIXEL_MODE){
      echo "
	$marker_js
	map.addMarker( marker, new Point($lat,$lon));
      ";
    }
  }
  
}

#
# Draw Building all the locations of the category with name "$cat"
# as layer points!!
#
function drawBuildingCatLayer($cats,$lls, $add_message=TRUE, $directions=TRUE){
  global $error, $fconf,$GPS_MODE,$PIXEL_MODE,$config,$FS_PRE;

  //$c = getCatFromName($cats[0]);

  // Fake layer name
  //$layer = $c->get_name();


  // Get category Image
  $cimg = "$FS_PRE/projects/$config/images/layers/test.png";
  $last = $cimg[strlen($cimg)-1];

  // Keep cimg always pointing correct
  $img_tmp = "";
  if (($cimg == "" || !file_exists ($cimg) || $last=="/")){
    $img_tmp = $cimg;
    $cimg="$FS_PRE/projects/$config/images/layers/test.png";
    $cimg_w=1;
    $cimg_h=1;
  }else{
    $info=getimagesize($cimg);
    $cimg_w=$info[0];
    $cimg_h=$info[1];
    $img_tmp = "TT";//$c->get_image_name();
  }

  $locs = getBuildingAllLocs();  

 foreach ($locs as $l){

    $lat=$l->get_lat()/1;
    $lon=$l->get_lon()/1;
  
    $name=$l->get_name();
    $cname="TT";//$c->get_name();
    
    $poly = $l->get_polygon();
    // check for blank marker 
    if (preg_match("/blank2/" , $img_tmp)>0){
      echo getLocTransPoly($l);
      continue;
    }
    
      // check for blank marker 
    if ($poly!="" && preg_match("/blank/" , $img_tmp)>0){
      $box = getBoundingBox($poly);
      $cimg_w=$box["max"]["x"] - $box["min"]["x"];
      $cimg_h=$box["max"]["y"] - $box["min"]["y"];
//       $notes="";
    }
	$clocs=array();
	foreach($cats as $cat){
		$cc = getCatFromName($cat);
		// Check that it exists
		  if ($cc->_new){
			//echo "$cat Does not exists";//需要注释掉，不然$cat会报错
			continue;
		  }
		$cs = getBuildingAllLocsForCategory($l->get_name(),$cc->id);
		$clocs[$cc->get_description()]=$cs;
		//file_put_contents("/var/www/RL3/php.log","cat=".$cat."\n",FILE_APPEND);
	}
	
	foreach($lls as $ll){
		$la = getLayerFromName($ll);
		// Check that it exists
		  if ($la->_new){
			//echo "$cat Does not exists";//需要注释掉，不然$cat会报错
			continue;
		  }
		$las = getBuildingLayerPointsFrom($l->get_name(),$la->id);
		$clocs[$la->get_description()]=$las;
		//file_put_contents("/var/www/RL3/php.log",count($las)."\n",FILE_APPEND);
	}
    $notes=getBuildingMarkerMessage($name,$clocs);
	
    $marker_js="
    var marker = {
	'layerName' : '$layer',
	'icon' : '$cimg',
	'icon_width' : $cimg_w,
	'icon_height' : $cimg_h,
	'pin_point' : {'x':$cimg_w/2, 'y':$cimg_h/2},
	'popup' : \"$name\"
    ";

    // ESCAPE for JSON
    $notes = replaceSQouteForJSON($notes);
    
    if ($notes!="")
      $marker_js.="	,'message': '$notes'";

    // Finalize marker
    $marker_js.="};";
    
    
    // Add it on the map
    if ($fconf->mode == $GPS_MODE){
      echo "
	$marker_js
	map.addMarker( marker, new LatLon($lat,$lon));
      ";
    }else if ($fconf->mode == $PIXEL_MODE){
      echo "
	$marker_js
	map.addMarker( marker, new Point($lat,$lon));
      ";
    }
  }
  
}
#
# Check if location $l is overlaping any other in the
# array arr. Th is distance between the points after which 
# no overlapping is considered.
# 
# $l must have lat lon
#
function overlaps($l, $arr, $th){
  
  foreach ($arr as $key=>$p){
    foreach ($p["marks"] as $m){
//       echo "---\n".getDistanceOA($l,$m)."\n";
//       echo "$l->lat $l->lon\n";
//       echo $m["lat"]." ".$m["lon"]."----\n";
      if (getDistanceAA($l,$m)<$th) return $key;
    }
  }
  
  return FALSE;
}



#
# Draw all the locations of the category with name "$cat"
# as layer points on the overview
#
function bubbleUpCatLayer($cat, $add_message=TRUE,$directions=TRUE){
  global  $error, $fconf,$GPS_MODE,$PIXEL_MODE,$config,$FS_PRE,$CAT_TYPE_BUBBLEUP,$CAT_TYPE_NOAUTO,$FS_PRE;
  
  $c = getCatFromName($cat);
  
  // Check that it exists
  if ($c->_new) return;
  // If bubbling is disabled return
  if (($c->type&$CAT_TYPE_BUBBLEUP)!=$CAT_TYPE_BUBBLEUP) return;
  
  
  // Fake layer name
  $cname=$c->get_name();
  

  // Get category Image
  $cimg = "$FS_PRE/projects/$config/images/".$c->get_image_name();
  $last = $cimg[strlen($cimg)-1];
  
  if ($cimg == "" | !file_exists ($cimg) | $last=="/"){
    $cimg="$FS_PRE/projects/$config/images/layers/blank.png";
    $cimg_w=0;
    $cimg_h=0;
  }else{
    $info=getimagesize($cimg);
    $cimg_w=$info[0];
    $cimg_h=$info[1];
  }
 
  $locs = getAllLocsForCategory($c->id);
  
  $allmarks = array();
  $ov = getOverviewFloor();
 foreach ($locs as $l){

    // Try to map this to the overview
    $overview_pos = getOverViewPos($l, true, $ov);
    if ($overview_pos===FALSE) continue;
    
    $lat=$overview_pos["lat"];
    $lon=$overview_pos["lon"];
    
  
    $name=$l->get_name();
    
    
    $mark = array();
    $mark["name"] = $name;
    $mark["loc"] = $l;
    $mark["lat"] = $lat;
    $mark["lon"] = $lon;
    $mark["desk"] = $l->get_description();
    $f = new floors($l->bf);
    $mark["floor"] = $f->get_floor();
	
    // Get more info
    $more_info="";
    if (hasMoreInfo($l->id, $FS_PRE)){
      $more_info=getMoreInfoWithMeta($l);
    }
    
    $mark["mf"] = $moreinfo;
    
    // Check overlapping 
    $idx = overlaps($overview_pos, $allmarks, $cimg_w);
    if ($idx === FALSE){
      $cont = array();
      $cont["marks"]=array();
      $cont["marks"][]=$mark;
      $allmarks[]=$cont;
    }else{
      // just append here
      $allmarks[$idx]['marks'][]=$mark;
    }
    
  } // End of parsing
  
  // Now go draw!
  foreach ($allmarks as $mark){
    $num = count($mark['marks']);
    
    // The first point
    $m = $mark["marks"][0];
    // Adding single marker
    if ($num==1){
	$notes="";
	if ($add_message)
	  $notes=getGenericMarkerMessage($m["loc"], "Floor ".$m["floor"], $m["mf"], $cname, $directions);
    }
    else {
      $notes="<h1>标记多个</h1>";
      $notes.="<div class=\"center\">";
      $count=0;
      foreach ($mark["marks"] as $m){
	$count++;
 	$onmouseover="onmouseover=\"$(\\'#".crc32($m["name"])."\\').show();\"";
	$ontouchstart="ontouchstart=\"$(\\'.mim\\').hide();$(\\'#".crc32($m["name"])."\\').show();\"";
// 	$onmouseout="";
	$onmouseout="onmouseout=\"$(\\'#".crc32($m["name"])."\\').hide();\"";
	
	$name = $m["name"];
	$newway = ((($c->type&$CAT_TYPE_NOAUTO) == $CAT_TYPE_NOAUTO) && $c->name!="");
	if ($newway) $name=$c->name." $count"; // Use the catname instead of location
	
	// Wrapper div for mouse events
	$notes.="<div $onmouseover $onmouseout $ontouchstart>";
	$notes.="<div class=\"mmitem\" >$name</br><span> 层 ".$m["floor"]."</span></div>";
	$notes.="<div id=\"".crc32($m["name"])."\" style=\"display: none\" class=\"mim\">".
		getMultiMarkerMessage($m["loc"],$m["decr"], $m["mf"], $cname,$directions).
		"</div>";
	$notes.="</div>";
	
      }
      $notes.="</div>";
    }
    
    $marker_js="
	var marker = {
	    'layerName' : '$cname',
	    'icon' : '$cimg',
	    'icon_width' : $cimg_w,
	    'icon_height' : $cimg_h,
	    'pin_point' : {'x':$cimg_w/2, 'y':$cimg_h/2},
	    'popup' : \"".$m["name"]."\"
	";

	
	if ($notes!="")
	  $marker_js.="	,'message': '$notes'";

	// Finalize marker
	$marker_js.="};";
	
	
	// Add it on the map
	if ($fconf->mode == $GPS_MODE){
	  echo "
	    $marker_js
	    map.addMarker( marker, new LatLon(".$m["lat"].",".$m["lon"]."));
	  ";
	}else if ($fconf->mode == $PIXEL_MODE){
	  echo "
	    $marker_js
	    map.addMarker( marker, new Point(".$m["lat"].",".$m["lon"]."));
	  ";
	}
  } // End of Drawing part
  
}


#
# Draw the path. Returns the vertical routing info or FALSE if no 
# path is found
#
function drawPath($catMode=FALSE){

  global $connection_graph_file,$connection_graph_file_cat,$mp_settings,$MAX_INC, $fconf, $connection,$config,$PIXEL_MODE,$loc,$g_mapState;
  
  $catMode = ($g_mapState->routecat!="");
  $vrouting = array();
  $vrouting["used"]=false;
  $vrouting["path_found"]=false;
  
  if (!$g_mapState->isRouting() && $catMode===FALSE) return;
  
  $mode="";
  $c_mode="GPS";
  if ($fconf->mode == $PIXEL_MODE){
    $mode="Px";
    $c_mode="PIXEL";
  }
  
  $stairs_post="0";
  $elev_post="0";
  //zz 修正寻路方法，电梯，楼梯，最短路径
  if ($_REQUEST['st_elev_gr'] == "stairs" ){
    $stairs_post="1";
    $elev_post="0";
  }else if ($_REQUEST['st_elev_gr'] == "elevator" ){
    $elev_post="1";
    $stairs_post="0";
  }else if ($_REQUEST['st_elev_gr'] == "all" ){
	$elev_post="1";
    $stairs_post="1";
  }

  $output="";

  $graph_file = $connection_graph_file;
  
  // User given search
  $tmp_destination = $g_mapState->room;
  
  // if it is alias use the real location name 
  $tmp_loc = new locations($g_mapState->rlid);
  $tmp_destination = $tmp_loc->name;
  
  $tmp_destination=name2DBFormat($tmp_destination);
  
  if ($catMode===TRUE){
    $graph_file = $connection_graph_file_cat;
    $tmp_destination=$g_mapState->routecat;
  }
  
  // Inclination
  $tmp_inc = $g_mapState->inc;
  if ($tmp_inc=="")  $tmp_inc = $MAX_INC;
  
  $cmd ="$graph_file -n $connection->db -u $connection->user -o $connection->host -p $connection->pw ";
  $cmd.=" -d \"".$tmp_destination."\" -s $stairs_post -e $elev_post -i $tmp_inc ";
  $cmd.=" -v ".$mp_settings->get_initial_node_id(). " --bf ". $fconf->id ;
  
  // Add new ids to enable name duplication
  if ($g_mapState->rlid!="") $cmd.=" --rlid $g_mapState->rlid";
  
  // Add in/out-doors
  if (isset($_REQUEST['rscale']) && $_REQUEST['rscale']!=0) 
    $cmd.=" -c ".$_REQUEST['rscale'];
  
  // YAH x,y
  if ($mp_settings->yah_x && $mp_settings->yah_y) 
    $cmd .= " -x" . $mp_settings->yah_x." -y" . $mp_settings->yah_y;

  // DB pass is here...
//    echo "\n<!-- $cmd-->\n";
  
  exec ( $cmd, $output, $rc );
  
  // Parse ALL
  $toline=count($output)-1;
  // Parse all but NOT THE LAST LINE (that is the selected closest location)
  if ($catMode===TRUE) $toline=count($output)-2; 
  
  // Check that we got a path
  if ($toline<=0) 
    return $vrouting;
  
  // We got something!
  $vrouting["path_found"]=true;
  
  $ignore=false;
  // Path Points
  $pp=0;
  $path_js="map.addPath([\n";
  
  // Path colors 
  $pcolour = $mp_settings->get_path_color();
  if (isOverview()) 
    $pcolour = $mp_settings->get_outpath_color();
  
  // For every Line of the output
  for ($i=0; $i<$toline; $i++){
    $exp_out.=$output[$i]."\n";
    $gps=explode(" ", $output[$i]);
//     echo "<!--".$output[$i]."-->\n";
    
    /*
     * Move commands
     */
    if ($gps[0]=="Move:"){

      
      // Valid moves are:
      // 1. first move on the current floorplan
     
	 
	 
      if (!$ignore && (!$vrouting['used']) ){
	// 
	$parts = explode("->",$gps[1]);
	$vrouting["from_node"] = $parts[0];
	$vrouting["to_node"] = $parts[1];
	$vrouting["next_bf"] = $gps[2];
	$f = new floors($gps[2]);
	$vrouting["next_b"] = $f->get_building();
	$vrouting["next_f"] = $f->get_floor();
	$vrouting["last_x"] = $old_p_x;
	$vrouting["last_y"] = $old_p_y;
	$vrouting["path_idx"] = $i;
	$vrouting["used"]=true;
      }
      
      // If we are on ignore, and the move is not to out floor
      if ($ignore && $gps[2]!=$fconf->id) continue;
      
      if ($gps[2]!=$fconf->id){
	$ignore=true;
	
	
	
	if ($pp>0){
	  $path_js=rtrim($path_js, ",");
	  $path_js.="],{'stroke-width': ".$mp_settings->get_path_thickness().", 'stroke-opacity': 0.9, 'stroke': '$pcolour', 'stroke-dasharray': '".$mp_settings->get_path_stroke()."'});";
	
	  echo $path_js;
	  $pp=0;
	  $path_js="\nmap.addPath([\n";
	}
	
      }else{
	$ignore=false;
	
	if ($pp>0){
	  $path_js=rtrim($path_js, ",");
	  $path_js.="],{'stroke-width': ".$mp_settings->get_path_thickness().", 'stroke-opacity': 0.9, 'stroke': '$pcolour', 'stroke-dasharray': '".$mp_settings->get_path_stroke()."'});";
	
	  echo $path_js;
	  $pp=0;
	  $path_js="\nmap.addPath([\n";
	}
      }
      
    }
    
    /*
     * All other commands
     */
    else {
      if ($ignore){
	continue;
      }
      
      $p_x=$gps[0]/1;
      $p_y=$gps[1]/1;

      if ($mode == "")
	$path_js.="\nnew LatLon($p_x, $p_y),";
      else
	$path_js.="\nnew Point($p_x, $p_y),";
	
      $pp++;
      $old_p_x=$p_x;
      $old_p_y=$p_y;
    }
  } // End of For (output)

  
  //if (!$txt_path) generateTextFromPath($output);
  $path_js=rtrim($path_js, ",");
  $path_js.="],{'stroke-width': ".$mp_settings->get_path_thickness().", 'stroke-opacity': 0.9, 'stroke': '$pcolour', 'stroke-dasharray': '".$mp_settings->get_path_stroke()."'});";
  
  if ($pp>0)
    echo $path_js;
  
  // Check and get the selected closest location!
  
  if ($catMode===TRUE){
    $g_mapState->room = $output[$toline+1];
    $loc=getLocFromName($g_mapState->room);
  }
  
  return $vrouting;
}



/**
 * Generate human readable text from the 
 * calculated path...
 * 
 * Set toTheEnd to TRUE to get the full path to the destination
 */
function generateTextFromPath($output, $toTheEnd=FALSE){
  global $fconf, $config, $TXT_INSTR;

  $orig_floor=$fconf;
  // Clean old 
  $TXT_INSTR = "";
  
  $v1 = new vertex();
  $gps=explode(" ", $output[0]);
  $v1->lat  = $gps[0]/1; $v1->lon  = $gps[1]/1;
  
  $v2 = new vertex();
  $gps=explode(" ", $output[1]);
  $v2->lat  = $gps[0]/1; $v2->lon  = $gps[1]/1;
  
  $v3 = new vertex();
  
  $ahead=FALSE;
  $ahead_conj=0;
  
  for ($i=2; $i<count($output)-1; $i++){
    // Clear
    $inst="";
    $v3 = new vertex();
    
    // get new point
    $gps=explode(" ", $output[$i]);
    if ($gps[0]=="Move:") {
      // Get Entrance of previous point
      $TXT_INSTR.= "<br/> - ".getStairsEntranceDestText($v2);
      if (!$toTheEnd) return;
      
      $fconf = new floors($gps[2]);
      
      continue;
      
    }
    $v3->lat  = $gps[0]/1; $v3->lon  = $gps[1]/1;
    
    // Get angle
    $f = getAngleOfVectors_rad($v1,$v2,$v3);
    $df = toDeg($f);
  
    // Get direction
    $dir = getDirection($v1,$v2,$v3,$df);
    
    // More than one path to follow?
    $is_conjunction=TRUE;
    $v22 = getVertexFromPos($v2->lat,$v2->lon, $fconf->id);
  
 

  
    if ($v22!==FALSE) {
      $v2=$v22;
      if (count(getNeigboorsOfVector_pureVec($v2)) < 3) 
	$is_conjunction=FALSE;
    }
	
    $s1_2 = hasStairs($v1, $v2);
    $s2_3 = hasStairs($v2, $v3);

    if ($dir=="Straight"){

      if ($ahead==TRUE) {
	if (!isOverview() && $is_conjunction){
	  $ahead_conj++;
	}
	$v1=$v2;
	$v2=$v3;
	continue;
      }
      $inst = "沿着走廊";   // KG 21/2/2012: Add line here - if building >1 then this, otherwise "Walk straight ahead" - implement after demo
      $ahead=TRUE;
    }else {
 
 
      if ($is_conjunction){
      
	// Get to point
	if (!isOverview() && $ahead) {
	  $all_loc = getList_locations(" bf=".$fconf->id);
	  $c=getCloserPoint_Array($v2, $all_loc);
	  $inst = "在 ".$c->name.", ";
	  // Remove this conjuntion
	  if ($is_conjunction) $ahead_conj--; 
	  if ($ahead_conj== 1) $inst.= " (下一个路口)"; //KG 6March,2012
	  if ($ahead_conj>1) $inst.= " (在 $is_conjunction 路口之后)";
	  
	}
	$inst .= "Turn $dir";
	$ahead=FALSE;
	$ahead_conj=0;
      }else
	$inst = "沿着路走 (走 $dir) ";
    }
 
 
    if ($s1_2) $inst.=" 楼梯后面";
    else if ($s2_3) $inst.=" 经过楼梯";
    $TXT_INSTR.= "<br/> - ".$inst;
    
    // Enable to debug
    //drawMarker($v2->lat, $v2->lon, $inst );
    
    $v1=$v2;
    $v2=$v3;
    
  }
  
	
  // Last point
  $v3 = new vertex();
  $gps=explode(" ", $output[count($output)-1]);
  $v3->lat  = $gps[0]/1; $v3->lon  = $gps[1]/1;
  $txt=getStairsEntranceDestText($v3);
  if ($txt!="") {
    $TXT_INSTR.= "<br/> - ".$txt;
    $f = getAngleOfVectors_rad($v1,$v2,$v3);
    $df = toDeg($f);
    $txt="目的地在你左边";
    if ($df < 0 ) $txt="目的地在你右边";
    $TXT_INSTR.= "<br/> - ".$txt;
  }
   
  // Restore floor
  $fconf=$orig_floor;
  	
}

function getStairsEntranceDestText($v2){
    global $fconf;
    $txt="";
    $v2 = getVertexFromPos($v2->lat,$v2->lon, $fconf->id);

    if ($v2===FALSE) return $txt;
    $lnk = $v2->get_link();
    
    // Deside enter/exit or just "go to"...
    $msg = "前往入口";

    if (($fconf->get_building() == "overview" ) || ($fconf->get_building() == "0"))
      $msg = "进入入口 ";
    else
      $msg = "从入口离开 ";

    
    if ($v2->assoc_type == "e") {
      $en = getEntranceById($v2->assoc_id);
      if ($en === FALSE) return $txt;
      $txt= $msg.$en->get_name(); 
    }
    else if ($lnk!="" && $lnk[0] == "n"){
      $txt= $msg.substr($v2->get_link(),2);
    }
    else if ($lnk!="" && $lnk[0] == "s"){
      $txt= "走楼梯";
    }else if ($v2->assoc_type == "l" ){
      $txt= "你到了！"; 
    }
    
    return $txt;
}

function hasStairs($v1, $v2){
  global $fconf;
  
  $v1 = getVertexFromPos($v1->lat,$v1->lon, $fconf->id);
  $v2 = getVertexFromPos($v2->lat,$v2->lon, $fconf->id);
  if ($v1===FALSE || $v2===FALSE) return FALSE;

  $ae = getEdgesFromEndPoints($v1, $v2);
  
  if (count($ae) == 0 ) return FALSE;
  
  foreach ($ae as $e){
    if ($e->stairs == "Y") return TRUE;
  }
  
  return FALSE;
}

function getDirection($v1,$v2,$v3,$df){
  global $STR8_THRES_DEG;
  // Direction
  $direct="";
  
  // Get directionallity
  $x_inc=$y_inc=FALSE;
  if ($v1->lon < $v2->lon) $y_inc=TRUE;
  if ($v1->lat < $v2->lat) $x_inc=TRUE;
  $x_inc2=$y_inc2=FALSE;
  if ($v2->lon < $v3->lon) $y_inc2=TRUE;
  if ($v2->lat < $v3->lat) $x_inc2=TRUE;
  
  
  

  if (abs($df) < $STR8_THRES_DEG) $direct = "Straight";
  else {
    // TODO: Find to point
    if ($df > 0) $direct= "left";
    else   $direct="right";
  }
  
  return $direct;
}


/**
 * Return the closest veector to verctor $v that
 * is associated with a location...
 */
function getClosestLocVectorFrom($v){

  // All vectors 
  $av = getList_vertex("bf=$fconf->id AND assoc_type='l'");
  
  return getCloserPoint_Array($v, $av, true);
  
}

function compressJS($js){
  $c_js=preg_replace("/\n/", "", $js); 
  $c_js=preg_replace("/\t/", "", $js);
  $c_js=preg_replace("/,\s/", ",", $js);
  $c_js=preg_replace("/var marker/", "var m", $js);
  $c_js=preg_replace("/\s[\s]+/", " ", $js); // multiple spaces
  return $c_js;
}

#
# Project TOOLS
#
function isOverview($fopt=null){
  global $fconf;
  
  // If fopt is provided check this
  // it is expected to be a floor object
  $f = $fconf;
  if ($fopt!=null) $f=$fopt;
  
  return ( $f->building == "0" ); 
}

function drawPathWarnings(){
  global $fconf,$sec,$STATUS_OK,$warn_img,$config,$FS_PRE;
  
  $all_e = getEdgesFloor($fconf->id,"e.status<>$STATUS_OK");
  
  // Find warning image from Layers table...
  // If not use the default
  $l = getLayerFromName("Notifications");
  if ($l->image_name!="")
    $warn_img = "projects/$config/images/".$l->image_name;
  
  foreach ($all_e as $e){
  
    $ep = getList_edge_points("eid=".$e->id);
    
    $px;$py;
    if (count($ep)>0){
      // Use the middle point of the edge
      $idx = floor(count($ep)/2);
      $px=$ep[$idx].get_lat();
      $py=$ep[$idx].get_lon();
    }else{
      // Use end points
      $sv=new vertex($e->get_start());
      $ev=new vertex($e->get_end());
      
      $px=($sv->get_lat() + $ev->get_lat())/2;
      $py=($sv->get_lon() + $ev->get_lon())/2;
    }
    
    $marker_js="
      var marker = {
	  'layerName' : 'warn',
	  'icon' : '$FS_PRE$warn_img',
	  'icon_width' : 32,
	  'icon_height' : 32,
	  'pin_point' : {'x':32/2, 'y':32/2},
	  'popup' : '".$e->get_status_msg()."',
	  'message' : '<h1>注意</h1><h2>".$e->get_status_msg()."</h2>',
      };";
      
      $marker_js .= "
	map.addMarker( marker, new Point($px,$py));
      ";
      
      echo compressJS($marker_js);
  }
}



/**
 * Broker: More Information helper to query the broker and
 * parse the reply. It returns the meta data as assoc. array.
 * Additionaly, this function is responsible to handle the
 * cache, check database data and update them.
 * 
 * @param loc The location object
 */
function getMetaData($loc){
  global $config,$mp_settings;
  
  $project=$config;
  $locname=$loc->get_name();
  $cat = new categories($loc->get_cid());
  
  $catname=$cat->get_name();
  
  // Time now... in string format compatible with mysql
  $now = date( 'Y-m-d H:i:s');
  
  // Check cache/DB
  if ($loc->get_meta()!="") {
    // last update in seconds from epoch
    $last_update = strtotime( $loc->get_meta_ts());
    $now_s = strtotime( $now);
    
    // Calc the diff and return from the local
    // database :)
    $diff = $now_s - $last_update;
    if ($diff < $mp_settings->get_meta_to()) 
      return json_decode($loc->get_meta(),TRUE);
  }
  
  
  // Form the full broker url
  $url =$mp_settings->get_broker_url()."project=$project&loc=".urlencode($locname)."&cat=".urlencode($catname);

  // Get and decode the JSON from the broker
  $meta = file_get_contents($url);
  $reply=json_decode($meta,TRUE);
  
  // Store in the DB... since cache is expired
  $loc->set_meta($meta);
  $loc->set_meta_ts($now);
  $loc->save();
  
  return $reply;
}

/**
 * Get the full more information content including the replacements
 * done from the broker (metadata)
 * 
 * @param loc The location object
 */
function getMoreInfoWithMeta($loc, $FS_PRE){
  $ff = new floors($loc->bf);
  $infoname=$ff->building . "_" . $ff->floor . "_" . $loc->name;
  // Check that it has more info...
  if (!hasMoreInfo($infoname,$FS_PRE)) return "";

  // Get and parse
  $nfo = getMoreInfoWithHead($infoname,$FS_PRE);
  
  
  $matches=array();
  if (preg_match_all("/\[meta:[^:]*:[^\]]+\]/",$nfo, $matches)==0) return $nfo;
  
  // Here we are sure that more info exist and that it
  // includes meta data...
  
  // Get meta-data from the brocker
  $meta = getMetaData($loc);
  if (isset($meta["error"])){
    return $nfo." <br/> 请报告这个错误:<br/>".$meta["error"];
  }
  
  // ... check for no meta...
  if (count($meta)==0) return $nfo;
  
  foreach ($matches[0] as $tag){
    $parts = explode(":", $tag);
    
    if (count($parts)!=3) continue;
    
    $variable = $parts[1];
    if (!isset($meta["$variable"])) continue;
    $attr = substr($parts[2],0,-1);
    if (!isset($meta[$variable][$attr])) continue;
    
    $replacement=$meta[$variable][$attr];
    // Escape HTML 
    if ($attr=="value" && $meta[$variable]["type"]=="html"){
      $replacement = do_replaces($replacement);
      $replacement = htmlentities($replacement,ENT_QUOTES);
    }
    
    $repres=preg_replace("/\[meta:$variable:$attr\]/",$replacement,$nfo);
    if ($repres!=NULL) $nfo=$repres;
  }
  
  return $nfo;
}




function getGPSMessage($lat, $lon, $title){

  // Check for theme-able message and call it if there
  if ( function_exists("getGPSMessageTheme")){
    return getGPSMessageTheme($lat, $lon, $title);
  } 
  
  
  $gps_message="<h1>$title</h1>";
//   $gps_message.="<input type=\"button\" class=\"msg_gmaps\" onmousedown=window.open(\"https://maps.google.co.uk/maps?q=$lat+$lon\") value=\"Google Maps!\"/><br/>";
  
  $sviewUrl = "https://maps.google.co.uk/maps?q=$lat+$lon&cbll=$lat,$lon&cbp=0,0,,0,0&layer=c";
  $gps_message.="<input type=\"button\" class=\"msg_streetview\"  onmousedown=window.open(\"$sviewUrl\") value=\"查找街道（通过Google）\"/><br/>";
  
  return $gps_message;
}

function drawGPSFix(){
  global $mp_settings, $fconf;
  
  if (!isset($_REQUEST["gps"]) || $_REQUEST["gps"]=="") return;
  
  
  
  $parts = explode(",",$_REQUEST["gps"]);
  $data = getFloorGPSData($fconf->id);
  
  
  $pos = GPS2Map($parts[0], $parts[1], $data);
  

  
  drawMarker($pos["x"], $pos["y"], "GPS","", $mp_settings->get_prev_pin(), "false", "gps", "", getGPSMessage($parts[0], $parts[1],"GPS You are Here!"));
  
  // Do not do it, instead use the YAH! (cause they may both exist)
  //echo "setCenter(".$pos["x"].", ".$pos["y"].");\n";
}

/**
 * Split Edge $e at (x,y) point (or close to)
 */
function splitedge($eid, $x, $y, $vid=NULL){
  global $connection, $SPLIT_VERTEX_THRES;
  
  // Get From DB
  $e = new edges($eid);
  if ($e->_new) return;
  
  // get vertices
  $s = new vertex($e->get_start());
  if ($s->_new) return;
  $t = new vertex($e->get_end());
  if ($t->_new) return;
  
  // WARNING:
  // Check that the split point is not to close to s or t
  $d = getDistanceOA($s, array("lat"=>$x, "lon"=>$y));
  if ($d<$SPLIT_VERTEX_THRES) return;
  $d = getDistanceOA($t, array("lat"=>$x, "lon"=>$y));
  if ($d<$SPLIT_VERTEX_THRES) return;
  
  
  
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
  
  // DO THE JOB :(
  // Create a new vertex
  $nv = new vertex();
  $nv->set_lat($x);
  $nv->set_lon($y);
  $nv->set_bf($s->get_bf());
  
  $p1 = $allpts[0];
  $p2 = $allpts[1];

  // Store prev bearing... (not used if only 2 points!)
  $bear = bearing($p2, $nv);
  // position in the points list
  $pos = 1;
  
  // Walk along the edge points
  for ($i=2; $i<count($allpts); $i++){
    // Bearing from the current point to $nv 
    // should be ~180 deg...
    $b = bearing($allpts[$i], $nv);
    
    echo abs($b-$bear)."<br/>";
    // Accept Point
    if (abs($b-$bear)>=178) {
      echo "Accepting<br/>";
      $p1 = $p2;
      $p2 = $allpts[$i];
      // position is exclusive (point $i belongs to the 2nd edge)
      $pos = $i;
      break;
    }
    
    // Shift points and set bearing
    //$p1 = $p2;
    $p2 = $allpts[$i];
    $bear = bearing($p2, $nv);
  }
  
  
  // ----^
  // Idea 2: calculate all the distances p1,nv + p2,nv for 
  // all the pairs and select the minimum pair (n^2 complexity)
  
  // Do the DB job

  // 2. Add vertex NV
  if ($vid==NULL) {
    // align the click point to the line... 
    $line = getLineFromPoints($p1, $p2);
    $newpos = projectionOnLine($nv,$line["a"],$line["b"]);
    $nv->set_lat($newpos['x']);
    $nv->set_lon($newpos['y']);
    $nv->save();
    $nvid = $connection->lastId();
    echo "Created $nvid\n";
  }else{
    $nv  = new vertex($vid);
    $nvid = $vid;
    echo "USED $nvid\n";
  }
  
  // 3. Add a new edge (get ID) S->NV
  $ne1 = new edges();
  $ne1->set_start($s->id);
  $ne1->set_end($nvid);
  $ne1->save();
  $ne1->id = $connection->lastId();
  // TODO: add this on save!
  $ne1->_new = false;

  
  // 4. Add edge Points and calc length
  $len = 0;
  for ($i=1; $i<$pos; $i++){
    $nep = new edge_points();
    $nep->set_lat($allpts[$i]->lat);
    $nep->set_lon($allpts[$i]->lon);
    $nep->set_eid($ne1->id);
    $nep->set_bf($t->get_bf());
    $nep->save();
//     echo "Add: $i\n $ne1->id";
    $len += getDistance($allpts[$i], $allpts[$i-1]);
  }
  // Add the lastpoint to NV distance (0-NV if no points)
  $len += getDistance($allpts[$pos-1], $nv);
  $ne1->set_length($len);
  $ne1->save();
  
  // 5. Add a new edge (get ID) NV->T
  $ne2 = new edges();
  $ne2->set_start($nvid);
  $ne2->set_end($t->id);
//   $ne2->set_stairs('Y');
  $ne2->save();
  $ne2->id = $connection->lastId();
  $ne2->_new = false;
  
  // 6. Add edge Points
  // Add the NV to 1st point (NV->t if no points)
  $len = getDistance($allpts[$pos], $nv);
  for ($i=$pos; $i<count($allpts)-1; $i++){
    $nep = new edge_points();
    $nep->set_lat($allpts[$i]->lat);
    $nep->set_lon($allpts[$i]->lon);
    $nep->set_eid($ne2->id);
    $nep->set_bf($t->get_bf());
    $nep->save();
    // add distance to the next one (if there is one)
    if ($pi+1<count($allpts))
      $len += getDistance($allpts[$i], $allpts[$i+1]);
  }
  $ne2->set_length($len);
  $ne2->save();
  
  // 1. delete the edge
  $e->delete();
  
}

function getIntersectionOfEdges($e1id, $e2id) {
  // Get edges
  $ap1 = getEdgeWithPointsArray($e1id);
  $ap2 = getEdgeWithPointsArray($e2id);
  if (($ap1===FALSE) || ($ap2===FALSE)) return FALSE;
  
  // Find the intersection point of each pair

  

  
  for ($i=1; $i<count($ap1); $i++){
    // First pair of first edge
    $p_11 = $ap1[$i-1];
    $p_12 = $ap1[$i];
    
    for ($j=1; $j<count($ap2); $j++){

      // Pair of second edge
      $p_21 = $ap2[$j-1];
      $p_22 = $ap2[$j];
      
      // get line intersection
      $point = findIntersectPoint($p_11,$p_12, $p_21,$p_22);
      
      // check parallel
      if ($point==null) continue;
      
      //echo "Point: ".$point["lat"]." - ".$point["lon"];
      if (!isPointOnLine($point,$p_11,$p_12) || !isPointOnLine($point,$p_21,$p_22)) 
	continue;
	
      // Point Found
//       echo "Point: (pos=".($i-1)."->$i,".($j-1)."->$j)".$point["lat"].",".$point["lon"]."\n";
      return $point;
    }
  }
  
  return FALSE;
}


//
// Moved here from indexes
//

/**
 * Default the map (used in error cases - "Not Found")
 */
function defaultMapSettings(){
  global $mp_settings, $fconf, $g_mapState;
  $fconf = new floors($mp_settings->get_default_bf());
  $v = new vertex($mp_settings->get_initial_node_id());
  $mp_settings->yah_x=$v->get_lat();
  $mp_settings->yah_y=$v->get_lon();
  
  // MapState
  $g_mapState->clearFromSeachRoom();
  $g_mapState->defaultYahBf($v->get_lat(),$v->get_lon(),$fconf->id,$mp_settings->get_initial_node_id());
}


/// Set map center only if there is no other operation
function setMapCenter() {
  global  $g_mapState, $vr;
  
  if ($vr==FALSE && $g_mapState->search=="" &&
      $g_mapState->from==""  && $g_mapState->room=="" &&
      $g_mapState->map_center!=""
      && $_REQUEST['gps']==""){
      echo "map.setCenter($g_mapState->map_center)";
  }
}

/// U ARE NOWHERE
function unsetYAH(){
  global $g_mapState;
  $g_mapState->yah_x=null;
  $g_mapState->yah_y=null;
  $g_mapState->yah_node_id=null;
}

/**
 * Project GLOBAL parsing function. All the common to all projects parameters 
 * should be parsed here. extra project params can be later parsed...
 * 
 * Set the following global variables:
 *   room, from, search, loc, loc_v, loc_error, more_info, routecat, center
 *   
 * May Modify:
 *  mp_settings: YAH
 *  fconf: change floor
 */
function parsePost(){

    global $loc, $connection,$popup, $loc_error,
	   $more_info, $fconf, $mp_settings,$config, 
	   $loc_v, $g_mapState, $g_cs;
    
    // Load settigs
    $g_cs = getMergedCS();
    
    // Get and parse the mapState object
    // NEW WAY!
    $tmpState = json_decode($_REQUEST['mapState'],TRUE);
    foreach ($tmpState as $k=>$v){
      // Over write only we we have a value
      if ($v==null || $v=="") continue;
      if (!property_exists($g_mapState,$k)) continue;
      $g_mapState->$k = $v;
    }
    
    // Clone REQUEST into mapState!
    // Backwards compatible
    foreach ($_REQUEST as $k=>$v){
      if ($k=="svg") {
	$g_mapState->svg=1;
	continue;
      }
      if ($v==null || $v=="") continue;
      if (!property_exists($g_mapState,$k)) continue;
      $g_mapState->$k = $v;
    }
    

    // Fix routing
    if ($g_mapState->isRouting() && $g_mapState->isCatRouting()) {
      $g_mapState->clearRoom();
    }
	
    // Check we are connected
    if (!$connection->isConnected()) {
      $error='无法连接: ' . $connection->last_error;
      die('无法连接: ' . $connection->last_error);
    }
    
    // Parse From...
    if ($g_mapState->hasFrom()) {
      $g_mapState->from=name2DBFormat(trim($_REQUEST["from"]));
      
      // if it is the first step
      if ($g_mapState->path_step==0){
      
	  // load from location
	  $loc_v = new locations(); // default to not check for null!
	  // 1. From flid
	  if ($g_mapState->flid!="") {
	    $loc_v = new locations($g_mapState->flid);
	    // If search has no name use the default locations' name
	    if ($g_mapState->from=="") $g_mapState->from=$loc_v->get_name();
	    if ($g_mapState->from!=$loc_v->get_name()) $loc_v->searched_as = $g_mapState->from;
	  }
	  // 2. From "search" name
	  if (!$loc_v || $loc_v->_new) {
	    $loc_v = getLocFromName($g_mapState->from);
	  }
      
	  // Check that is not new
	  if ($loc_v->_new===FALSE) { 
	    $mp_settings->yah_x = $loc_v->lat;
	    $mp_settings->yah_y = $loc_v->lon;
	    $fconf = new floors($loc_v->bf);
	    // Set state
	    $g_mapState->bf_setby="from";
	    $g_mapState->bf=$fconf->id;
	    $g_mapState->yah_setby="from";
	    unsetYAH();
	  }
	  else{
	    $loc_error.=" 开始地点 '$g_mapState->from'找不到<br/>";
	    defaultMapSettings();
	  }
      }
    }
    
    // Parse search...
    else if ($g_mapState->isSearch()) 
    {
      $g_mapState->search=name2DBFormat(trim($_REQUEST["search"]));
      
      // Check for layer enabling...
      if ($g_mapState->isSearchLayerEnable()) {
	$_REQUEST[$g_mapState->getSearchLayerURL()]="on";
	unset($_REQUEST["search"]);
	$g_mapState->search="";
	$g_mapState->slid=null;
	return;
      }
      

      // Load search location here
      $loc_v = new locations(); // default to not check for null!
      // 1. From slid
      if ($g_mapState->slid!="") {
	$loc_v = new locations($g_mapState->slid);
	// If search has no name use the default locations' name
	if ($g_mapState->search=="") $g_mapState->search=$loc_v->get_name();
	if ($g_mapState->search!=$loc_v->get_name()) $loc_v->searched_as = $g_mapState->search;
      }
      // 2. From "search" name
      if (!$loc_v || $loc_v->_new) {
	$loc_v = getLocFromName($g_mapState->search);
      }
      // 3. From QR id
      if ($g_mapState->qr!="" && $g_mapState->qr!=null) {
	$loc_v = getLocFromQR($g_mapState->qr);
	$g_mapState->search=$loc_v->get_name();
      }
      
      // Check that is not new
      if ($loc_v && !$loc_v->_new) {
      
	// Here check for 2 stage search
	$twoStage = is2StageSearch($loc_v);
	
	// Default bahaviour here
	if ($twoStage===FALSE) 
	  $fconf = new floors($loc_v->bf);
	else 
	  $fconf = getOverviewFloor();
	  
	
	$g_mapState->bf_setby="search";
	$g_mapState->bf=$fconf->id;
	unsetYAH();
	
      }
      else{
	$loc_error.=" 地点 '$g_mapState->search' (QR id=$g_mapState->qr) 无法被找到<br/>";
	defaultMapSettings();
      }
      
    }
    

    // Destination room
    if ($g_mapState->room!="") {
      $g_mapState->room=name2DBFormat($_REQUEST['room']);
    
      // Handle room field if there...
      if ($g_mapState->rlid!="") {
	$loc = new locations($g_mapState->rlid);
	if ($g_mapState->room=="") $g_mapState->room=$loc->get_name();
	if ($g_mapState->room!=$loc->get_name()) $loc->searched_as = $g_mapState->room;
      }
      
      // check by name
      if (!$loc || $loc->_new ) {      
	// GET Location...
	$loc = getLocFromName($g_mapState->room);
	$g_mapState->rlid = $loc->id;
      }

      if ($loc->_new){
	  $loc_error="地点\"$g_mapState->room\" 找不到!!!"; 
	  return;
      }

      // Get BF info
      $lbf = new floors($loc->get_bf());
      $popup=$loc->get_name().", 层: ".$lbf->get_floor().", 信息: ".$loc->get_description();
      
	
      // Check and set floor (ignore bf if no From)
      // TODO: Make a function in map state
      if (!$g_mapState->hasFrom() && $g_mapState->path_step<1
         && $g_mapState->yah_x==""){
	$fconf=getOverviewFloor();
	$v = new vertex($mp_settings->get_initial_node_id());
	$mp_settings->yah_x=$v->get_lat();
	$mp_settings->yah_y=$v->get_lon();
	$g_mapState->defaultYahBf($v->get_lat(),$v->get_lon(),$fconf->id,$mp_settings->get_initial_node_id());
	$g_mapState->bf_setby = "routing_step0";
      }
    }
      
// 	error_log($g_mapState);
    
    // Check More info:
    // 1. if routing give priority
    // 2. else load more info from search
    if ($loc && hasMoreInfo($loc->id)) $more_info=getMoreInfoWithMeta($loc);
    else if ($loc_v && hasMoreInfo($loc_v->id)) $more_info=getMoreInfoWithMeta($loc_v);
}

function is2StageSearch($loc){
  global $g_mapState;

  // First if all check if it was from "from"!
  if ($g_mapState->search=="" && $g_mapState->from!="") return FALSE;
  
  // Check step, if 2nd move on!
  if ($g_mapState->path_step>0) return FALSE;

  // Check for overview location
  $f = new floors($loc->get_bf());
  if (isOverview($f)) return FALSE;
  
  // Check that the floor has scaling info
  $opos = getOverViewPos($loc, FALSE);
  
  if ($opos===FALSE) return FALSE;
  
  return $opos;
}

/**
 * Creates Layers Table HTML
 */
function initLayers(){
    global $layers_html, $error, $config, $FS_PRE;

    // GET all layers...
    $all_layers =array_merge(getEnabledLayers(), getLayerCats());
    // Comparison function
    function cmp($a, $b) {
	$aname=$a->get_name();
	$bname=$b->get_name();
	return strcmp($aname,$bname);
    }
    // ... and sort them by name
    uasort($all_layers,'cmp');
    


    // ----- Layers
    //$layers_html="<fieldset> <legend><b>Select Layers</b></legend><hr/>";
    $layers_html = "<br/>";
    $layers_html.="<table>";
    foreach ($all_layers as $l){
	      
      // Parse row
      $sel = "";
      $lname=$l->get_name();
      $ldesc=$l->get_description();
      $limg=$l->get_image_name();
      
      // Deside prefix based on if it is cat or layer
      $pre="l__";
      if (isset($l->pid)) $pre="c__";
      
      $lid = str_replace(" ","_", "$lname");
      
      if (isset($_REQUEST["$pre$lid"])) {
	// Create Layers post for drawImage
	$sel="checked";
      }
      $cimg="$FS_PRE/projects/$config/images/$limg";
      if (!file_exists ($cimg) || !is_file($cimg))
	$cimg="$FS_PRE/projects/$config/images/layers/blank.png";
	
      $layers_html.="<tr>
		      <td > <img class='layer_image' src='$cimg'/></td>
		      <td>$ldesc</td>
		      <td><input type='checkbox' class='styled' name='$pre$lid' id='$pre$lid' $sel/>  </td>
		    </tr>";
    }
    
    
    // ----- Categories as layers

		    
    $layers_html.="</table>";
    $layers_html.="<br/>";
}


/**
 * Initializes 3 global variables:
 * 1. viewing_floor
 * 2. fp_opts: floors as select options
 * 3. fp_links: floors as links
 */
function initFloorPlans() {
    global $fp_links, $viewing_floor, $error, $config,$fconf,$FLOOR_SHOW_FLOOR,$FLOOR_LIST_OVERVIEW,$fp_opts;
	
    $all_floors = getAllFloorsSorted(isOverview());

    foreach ($all_floors as $f){
    
      // Set the current viewing floor
      if ($fconf->id == $f->id){
	$viewing_floor = $f->get_description();
	if ($f->get_building() != "0")  $viewing_floor = $f->get_description() ;
	
	// Maybe fix here later
	if (!isOverview() || ($f->get_type()&$FLOOR_SHOW_FLOOR)==$FLOOR_SHOW_FLOOR)  {
	    $viewing_floor .= ", 第" .$f->get_floor()."层";
	}
      }
      
      // Show overview first - building ==0, always reset zoom and center
      if ($f->get_building()=="0" && $f->get_description()!="") {
	
	$tmp .= "<a class = 'floorplan_button' data-value='".$f->id."' reset-center='1'>";
	$tmp .= $f->get_description();
	$tmp .= "</a><br/>";
	
	$fp_links=$tmp.$fp_links;
	
	// DropDown
	$fp_opts="<option value='$f->id' reset-center='1'>".$f->get_description()."</option>".$fp_opts;
	continue;
      }
      
      // Break conditions... 
      //  - if we are on overview consult the flag
      //  - if we are in a building show only floors from this building
      if (isOverview() && (($f->get_type()&$FLOOR_LIST_OVERVIEW)!=$FLOOR_LIST_OVERVIEW)) continue;
      if (!isOverview() && $f->get_building()!=$fconf->get_building()) continue;
      
      
      // Reset center flag when we change to different building...
      $reset_center=0;
      if ($f->get_building()!=$fconf->get_building())
		$reset_center=1;
	
      $fp_links .= "<a class = 'floorplan_button' data-value='".$f->id."' reset-center='$reset_center'>";
      $fp_opts.="<option value='$f->id' reset-center='$reset_center'>";
      
      // Viewing a room
      if ($f->get_room()!=""){
	  $fp_links .= "".$f->get_room().",".$f->get_building()."(".$f->get_floor().")";
	  $fp_opts .= "".$f->get_room().",".$f->get_building()."(".$f->get_floor().")";
	  
      // Normal floor
      }else {
	  // Always show description
	  $tmp_fname=$f->get_description().", 第" .$f->get_floor()."层";
	  
	  // If Indoors
	  if (!isOverview()) {
	    // Append floor only if the flag is set 
	    // WARNING: This has to be inner if in order to not move to the
	    // following if! (using &&)
	    if (($f->get_type()&$FLOOR_SHOW_FLOOR)==$FLOOR_SHOW_FLOOR)
	      $tmp_fname .= ", Floor " .$f->get_floor();
	      
	  
	  }
	  // Else If the floor has the flag
	  else if (($f->get_type()&$FLOOR_SHOW_FLOOR)==$FLOOR_SHOW_FLOOR) {
	      $tmp_fname .= ", Floor " .$f->get_floor();
	  }
	  // If displayin only description accept till 1st comma
	  else{
	    $tmp_fname = explode(',',$tmp_fname);
	    $tmp_fname = $tmp_fname[0];
	  }
	  
	  
	  $fp_links .= $tmp_fname;
	  $fp_opts  .= $tmp_fname;
	  
      }
      $fp_opts .= "</option>";
      $fp_links .= "</a></br>";
       
    }
}


function getTileCSS(){
  global $mp_settings;
  if (isOverview() && $mp_settings->tiles_out!=""){
    return "background-color: $mp_settings->tiles_out;";
  }
  else if (!isOverview() && $mp_settings->tiles_in!=""){
    return "background-color: $mp_settings->tiles_in;";
  }
  
  return "";
}

/**
 * Return the correct path depending on if SVG is used
 */
function getTilesPath(){
  global $fconf,$config,$g_mapState;
  
//   if ($g_mapState->svg!=1) return $fconf->tiles;
//   if (isset($_REQUEST['live'])) 
//     return "lib/php/makeSVG.php?building=$fconf->building&floor=$fconf->floor&project=$config&filename=std";
//   
//   $path = "projects/$config/svgs/$fconf->building$fconf->floor/${config}_".$fconf->building."_".$fconf->floor."_edited.svg";
//   if (file_exists($path)) return $path;
//   
//   return "projects/$config/svgs/$fconf->building$fconf->floor/${config}_".$fconf->building."_".$fconf->floor."_final.svg";
  
  return getTilesPathFrom($fconf,$config,$g_mapState);
}

function getTilesPathFrom($fconf,$config,$g_mapState){
  
  if ($g_mapState->svg!=1) return $fconf->tiles;
  if (isset($_REQUEST['live'])) 
    return "lib/php/makeSVG.php?building=$fconf->building&floor=$fconf->floor&project=$config&filename=std";
  
  $path = "projects/$config/svgs/$fconf->building$fconf->floor/${config}_".$fconf->building."_".$fconf->floor."_edited.svg";
  if (file_exists($path)) return $path;
  
  return "projects/$config/svgs/$fconf->building$fconf->floor/${config}_".$fconf->building."_".$fconf->floor."_final.svg";
}


function getRefreshURL(){
  global $SRV,$config,$g_mapState,$DEFAULT_CONFIG;
  
  // if page == index.php do not append
  $page="";
  $tmpf = basename($_SERVER['PHP_SELF']);
  if ($tmpf!="index.php") $page="/".$tmpf;
  
  // If config == default do not append
  $cfg_string = "";
  if ($config!=$DEFAULT_CONFIG) $cfg_string="config=".$config;
  
  // Http is needed here. TODO:FIXME: for https....
  $ret = "http://".$SRV['PUBLIC_HOSTNAME'].$SRV['WEBROOT'].$page;
  
  if ($cfg_string!="" || $g_mapState->svg==1) $ret .= "?".$cfg_string;
  
  if ($g_mapState->svg==1) $ret=appendParamURL($ret,"svg","1");
  if ($g_mapState->lite!="") $ret=appendParamURL($ret,"lite","1");
  
  if (isset($_REQUEST["a"]) && $_REQUEST["a"]=="psou") $ret=appendParamURL($ret,"a","psou");
  
  return $ret;
}

function appendParamURL($url,$param,$val){
  $ret=$url;
  if (strpos($url,"?")===FALSE) $ret.="?";
  $ret.="&$param=$val";
  return $ret;
}

function getBuildingsOptions(){
  
  $b = getList_floors("","ORDER BY description");
  
  $old_desc="";
  $opts="";
  
  foreach ($b as $f){
    if ($f->description == $old_desc) continue;
    // skip overview
    if ($f->building == 0) continue;
    
    $opts.="<option value='$f->id' data-value='$f->building'>$f->description</option>";
    $old_desc=$f->description;
  }
  
  return $opts;
  
}

/**
 * Return the ^2 floorplan dimensions given the image
 * ones (selects the larger)
 */
function getFloorDimPow2(){
  global $fconf;
  return getFloorDimPow2For($fconf);
}

function getFloorDimPow2For($fconf){
  $w=upper_power_of_two($fconf->width);
  $h=upper_power_of_two($fconf->height);
  
  if ($w>$h) return $w;
  
  return $h;
}

function getGooAnal(){
  global $config,$FS_PRE;
  
  $fname = "$FS_PRE/projects/$config/php/ga.js";
  
  if (file_exists($fname)) echo file_get_contents($fname);
}

function getHelp(){
  global $FS_PRE;
  
  $l = getLocFromName("__help");
  if (!$l || $l->_new)  return "";
  
  $help=getMoreInfoWithHead($l->id,$FS_PRE);

  return $help;
}



?>
