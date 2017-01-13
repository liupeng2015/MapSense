<?php

#
# Import Function Constants...
#

/// Settings PHP
// Suppress errors!
error_reporting(E_ALL);
// Increase MEMORY!
ini_set('memory_limit', '1G'); # MEM HUNGRY
// paths
$new_paths = "..".PATH_SEPARATOR."../db";
set_include_path(get_include_path() . PATH_SEPARATOR . $new_paths);


/// CLI Helpers
// ------------------------------------------------------------------------------------
global $EoL,$TAB;
// End of line: \n for cli <br/> for non-cli
$EoL="<br/>";
$TAB="&nbsp;&nbsp;&nbsp;&nbsp;";

$sapi = php_sapi_name();
if ($sapi==="cli"){
  # Convert command line args to $_REQUEST
  parse_str(implode('&', array_slice($argv, 1)), $_REQUEST);
  $EoL="\n";
  $TAB="  ";
}

// Use requested SAPI: Repetition....
if (isset($_REQUEST['sapi'])) {
  $EoL="<br/>";
  $TAB="&nbsp;&nbsp;&nbsp;&nbsp;";
  $sapi = $_REQUEST['sapi']; 
  if ($sapi==="cli"){
    # Convert command line args to $_REQUEST
    parse_str(implode('&', array_slice($argv, 1)), $_REQUEST);
    $EoL="\n";
    $TAB="  ";
  }
}
// ------------------------------------------------------------------------------------


$TOOLsDIR="../../../Tools/";



/// Constants
// Debug
$DEBUG=true;

// P2P
//KG added
$EDIT_EXT="_edited.svg";

$P2P_EXT="_p2p.svg";
$SCAN_EXT=".w2g";
$GEO_EXT="_geo.w2g";
$SNAP_EXT="_snap.w2g";
$FINAL_EXT=".svg";
$NUM_SAMPLES=10;
$UPLOAD_DIR = dirname(__FILE__)."/uploaded";
$PROC_DIR = dirname(__FILE__)."/processed";
$DIR = dirname(__FILE__);

// Default PNG size...
$IMG_DIM="4096";

// Indoors pixels per meter
$PPM_IN=32;
// Outdoors pixels per meter
$PPM_OUT=2;


//area around polygon over which to scan for associated points - 
//speeds process for large drawings (out of 1024)
$geo_area = 100; 


$orange  = "#F59D44";
$white = "#FFFFFF";
$grey = "#B2B2B2";
$blue = "#6699CC";

$use_raw_svg = FALSE; //flag to not apply deafult colours if set to true
$txt2loc_centre = FALSE; //flag to make text position equal to cof of location
$usetext = TRUE; //flag to indicate whether room text should not be put into SVG_info if FALSE, just "" put in SVG value
//This is populated later after master database search - just a placeholder on import

//global variables from GUI eventually


$font_colour = "#FFFFFF";
$font_family = "Arial";

//Colours for floorplan outline
$outline_fill = "99cccc";
$outline_stroke = "#FFFFF";
$outline_stroke_w = "0";

$polygon_stroke = "#D3BF96"; //lancaster "#FDFADA"; 
$polygon_stroke_w = "0.5"; 	
$polygon_fill = "#000000"; //"#308DCF";

///Edges
$path_colour = "none";    //"#FFFFFF";

$background_colour = "#D3BF96"; //lancaster"#CFC7B4";
$door_fill = "#FFFFFF";			//white for Lancaster,Aston
$door_color = "#D3BF96";    //lancaster "#272C2D" - charcoal;
$door_stroke = $polygon_stroke_w/2;//Make stroke width same as polygon for proper door alignment
$door_size = 1; //length of triangle edges for doors (1 for Aston, Lancaster)
$door_type = 0;

$entrance_size = "2"; //length of triangle edges for entrances (2 for Aston, Lancaster)
$entrance_fill = "#445937";	  //entrance fill colour
$entrance_color = "#AAA38E";  //line colour
$entrance_stroke = "0.4";
$entrance_type = 0;

//Snap to guidrails parameters

$DOOR_ROOM_OFFSET=floatval($door_size); //Automatically calculate offset based on door size
$ENTRANCE_ROOM_OFFSET = floatval($entrance_size); //Automatically calculate offset based on entrance door size
$DOOR_COL_OFFSET=4;
  




$CONF_DIR="/etc/location_db/";
$INSTALL_DIR="/var/www/RL3/projects";

// DB
$db_host="localhost";
$db_user="root";
$db_pass="admin";

$LAYERIMAGES = array();
$LAYERIMAGES["Entrances"]="/layers/entr.png";
$LAYERIMAGES["Stairs"]="/layers/stairs.png";
$LAYERIMAGES["Recycling"]="layers/Recycling_24px.png";
$LAYERIMAGES["Bus Stops"]="/layers/bus.png";
$LAYERIMAGES["Taxi"]="/layers/taxi.png"; 
$LAYERIMAGES["Food"]="/layers/food.png"; 
$LAYERIMAGES["Elevators"]="/layers/elev.png";
$LAYERIMAGES["Notifications"]="/layers/warning.png"; 
$LAYERIMAGES["Slopes"]="/layers/slope_medium.png"; 
$LAYERIMAGES["Male Toilets"]="/layers/toilet_male.png";
$LAYERIMAGES["Female Toilets"]="/layers/toilet_female.png";
$LAYERIMAGES["Disabled Toilets"]="/layers/toilet_disabled.png";
$LAYERIMAGES["Unisex Toilets"]="/layers/toilet_unisex.png";

$LAYERS = array();
$LAYERS["Entrances"]["image"]="/layers/entr.png";
$LAYERS["Entrances"]["description"]="出入口";
$LAYERS["Stairs"]["image"]="/layers/stairs.png";
$LAYERS["Stairs"]["description"]="楼梯";
$LAYERS["Recycling"]["image"]="layers/Recycling_24px.png";
$LAYERS["Recycling"]["description"]="回收点";
$LAYERS["Bus Stops"]["image"]="/layers/bus.png";
$LAYERS["Bus Stops"]["description"]="公交车站";
$LAYERS["Taxi"]["image"]="/layers/taxi.png"; 
$LAYERS["Taxi"]["description"]="出租车"; 
$LAYERS["Food"]["image"]="/layers/food.png"; 
$LAYERS["Food"]["description"]="餐饮"; 
$LAYERS["Elevators"]["image"]="/layers/elev.png";
$LAYERS["Elevators"]["description"]="电梯";
$LAYERS["Notifications"]["image"]="/layers/warning.png"; 
$LAYERS["Notifications"]["description"]="警告"; 
$LAYERS["Slopes"]["image"]="/layers/slope_medium.png"; 
$LAYERS["Slopes"]["description"]="斜坡"; 
$LAYERS["Male Toilets"]["image"]="/layers/toilet_male.png";
$LAYERS["Male Toilets"]["description"]="/layers/toilet_male.png";
$LAYERS["Female Toilets"]["image"]="/layers/toilet_female.png";
$LAYERS["Female Toilets"]["description"]="/layers/toilet_female.png";
$LAYERS["Disabled Toilets"]["image"]="/layers/toilet_disabled.png";
$LAYERS["Disabled Toilets"]["description"]="/layers/toilet_disabled.png";
$LAYERS["Unisex Toilets"]["image"]="/layers/toilet_unisex.png";
$LAYERS["Unisex Toilets"]["description"]="/layers/toilet_unisex.png";

// Layer types (to override the obsolete above!):
// ie:
// S(stairs): (No CID, add layer, add vertex)
// TM(male loo): (CID, No Layer, add vertex)
// D(door): (No CID, No layer, add vertex)
// 
$SVG_LAYER_TYPES = array();
//     "lifts" => "L",
$SVG_LAYER_TYPES["lifts-w2g"] = array();
$SVG_LAYER_TYPES["lifts-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["lifts-w2g"]["layer"] = true;
$SVG_LAYER_TYPES["lifts-w2g"]["layer_name"] = "Elevators";
$SVG_LAYER_TYPES["lifts-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["lifts-w2g"]["w2g"] = "L";
$SVG_LAYER_TYPES["lifts-w2g"]["fill"] = "#589623";		
$SVG_LAYER_TYPES["lifts-w2g"]["link"] = "e:";
$SVG_LAYER_TYPES["lifts-w2g"]["cat_name"] = "Elevators";
//     "stairs" => "S",
$SVG_LAYER_TYPES["stairs-w2g"] = array();
$SVG_LAYER_TYPES["stairs-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["stairs-w2g"]["layer"] = true;
$SVG_LAYER_TYPES["stairs-w2g"]["layer_name"] = "Stairs";
$SVG_LAYER_TYPES["stairs-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["stairs-w2g"]["w2g"] = "S";
$SVG_LAYER_TYPES["stairs-w2g"]["fill"] = "#F2AF00";//orange"#F2AF00";
$SVG_LAYER_TYPES["stairs-w2g"]["link"] = "s:";
$SVG_LAYER_TYPES["stairs-w2g"]["cat_name"] = "Stairs";
//     "entrances" => "e",
$SVG_LAYER_TYPES["entrances-w2g"] = array();
$SVG_LAYER_TYPES["entrances-w2g"]["vertex"] = true;
$SVG_LAYER_TYPES["entrances-w2g"]["layer"] = true;
$SVG_LAYER_TYPES["entrances-w2g"]["layer_name"] = "Entrances";
$SVG_LAYER_TYPES["entrances-w2g"]["cid"] = false;
$SVG_LAYER_TYPES["entrances-w2g"]["w2g"] = "e";
$SVG_LAYER_TYPES["entrances-w2g"]["link"] = "n:";

//     "bentrances" => "be", bridge entrances
$SVG_LAYER_TYPES["bentrances-w2g"] = array();
$SVG_LAYER_TYPES["bentrances-w2g"]["vertex"] = true;
$SVG_LAYER_TYPES["bentrances-w2g"]["layer"] = true;
$SVG_LAYER_TYPES["bentrances-w2g"]["layer_name"] = "Entrances";
$SVG_LAYER_TYPES["bentrances-w2g"]["cid"] = false;
$SVG_LAYER_TYPES["bentrances-w2g"]["w2g"] = "e";
$SVG_LAYER_TYPES["bentrances-w2g"]["link"] = "n:";

//     "toilet-male" => "TM",
$SVG_LAYER_TYPES["toilet-male-w2g"] = array();
$SVG_LAYER_TYPES["toilet-male-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["toilet-male-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["toilet-male-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["toilet-male-w2g"]["w2g"] = "TM";
$SVG_LAYER_TYPES["toilet-male-w2g"]["fill"] = "#144682";
$SVG_LAYER_TYPES["toilet-male-w2g"]["cat_name"] = "Toilets (Male)";
//     "toilet-female" => "TF",
$SVG_LAYER_TYPES["toilet-female-w2g"] = array();
$SVG_LAYER_TYPES["toilet-female-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["toilet-female-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["toilet-female-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["toilet-female-w2g"]["w2g"] = "TF";
$SVG_LAYER_TYPES["toilet-female-w2g"]["fill"] = "#EA3D7E";
$SVG_LAYER_TYPES["toilet-female-w2g"]["cat_name"] = "Toilets (Female)";
//     "toilet-disabled" => "TD",
$SVG_LAYER_TYPES["toilet-disabled-w2g"] = array();
$SVG_LAYER_TYPES["toilet-disabled-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["toilet-disabled-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["toilet-disabled-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["toilet-disabled-w2g"]["w2g"] = "TD";
$SVG_LAYER_TYPES["toilet-disabled-w2g"]["fill"] = "#73C3EA";
$SVG_LAYER_TYPES["toilet-disabled-w2g"]["cat_name"] = "Toilets (Accessible)";
//     "toilet-other" => "TO",
$SVG_LAYER_TYPES["toilet-other-w2g"] = array();
$SVG_LAYER_TYPES["toilet-other-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["toilet-other-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["toilet-other-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["toilet-other-w2g"]["w2g"] = "TO";
$SVG_LAYER_TYPES["toilet-other-w2g"]["fill"] = "#323232"; //yellow so they show up as problem toilets - ie don't know
$SVG_LAYER_TYPES["toilet-other-w2g"]["cat_name"] = "Toilets (Unisex)";
//     "toilet-unisex" => "TU",
$SVG_LAYER_TYPES["toilet-unisex-w2g"] = array();
$SVG_LAYER_TYPES["toilet-unisex-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["toilet-unisex-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["toilet-unisex-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["toilet-unisex-w2g"]["w2g"] = "TU";
$SVG_LAYER_TYPES["toilet-unisex-w2g"]["fill"] = "#679E9D";
$SVG_LAYER_TYPES["toilet-unisex-w2g"]["cat_name"] = "Toilets (Unisex)";
//     "doors" => "D",
$SVG_LAYER_TYPES["doors-w2g"] = array();
$SVG_LAYER_TYPES["doors-w2g"]["vertex"] = true;
$SVG_LAYER_TYPES["doors-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["doors-w2g"]["cid"] = false;
$SVG_LAYER_TYPES["doors-w2g"]["w2g"] = "D";
//     "vdoors" => "VD",
$SVG_LAYER_TYPES["vdoors-w2g"] = array();
$SVG_LAYER_TYPES["vdoors-w2g"]["vertex"] = true;
$SVG_LAYER_TYPES["vdoors-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["vdoors-w2g"]["cid"] = false;
$SVG_LAYER_TYPES["vdoors-w2g"]["w2g"] = "VD";
//     "bus-stops" => "BS",
$SVG_LAYER_TYPES["bus-stop"] = array();
$SVG_LAYER_TYPES["bus-stop"]["vertex"] = false;
$SVG_LAYER_TYPES["bus-stop"]["layer"] = false;
$SVG_LAYER_TYPES["bus-stop"]["cid"] = true;
$SVG_LAYER_TYPES["bus-stop"]["w2g"] = "BS";
$SVG_LAYER_TYPES["bus-stop"]["fill"] = "none";
$SVG_LAYER_TYPES["bus-stop"]["cat_name"] = "Bus Stops";
//     "food-drink" => "FD",
$SVG_LAYER_TYPES["food-drink"] = array();
$SVG_LAYER_TYPES["food-drink"]["vertex"] = false;
$SVG_LAYER_TYPES["food-drink"]["layer"] = false;
$SVG_LAYER_TYPES["food-drink"]["cid"] = true;
$SVG_LAYER_TYPES["food-drink"]["w2g"] = "FD";
$SVG_LAYER_TYPES["food-drink"]["fill"] = "none";
$SVG_LAYER_TYPES["food-drink"]["cat_name"] = "Food and Drink";
//     "buildings" => "BG",
$SVG_LAYER_TYPES["buildings-w2g"] = array();
$SVG_LAYER_TYPES["buildings-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["buildings-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["buildings-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["buildings-w2g"]["w2g"] = "BG";
$SVG_LAYER_TYPES["buildings-w2g"]["fill"] = "none";
$SVG_LAYER_TYPES["buildings-w2g"]["cat_name"] = "Buildings";
//     "buildings with indoor map" => "BGC",
$SVG_LAYER_TYPES["buildings-covered"] = array();
$SVG_LAYER_TYPES["buildings-covered"]["vertex"] = false;
$SVG_LAYER_TYPES["buildings-covered"]["layer"] = false;
$SVG_LAYER_TYPES["buildings-covered"]["cid"] = true;
$SVG_LAYER_TYPES["buildings-covered"]["w2g"] = "BGC";
$SVG_LAYER_TYPES["buildings-covered"]["fill"] = "none";
$SVG_LAYER_TYPES["buildings-covered"]["cat_name"] = "Buildings Covered";

//     "Car Parks" => "CP",
$SVG_LAYER_TYPES["car-park"] = array();
$SVG_LAYER_TYPES["car-park"]["vertex"] = false;
$SVG_LAYER_TYPES["car-park"]["layer"] = false;
$SVG_LAYER_TYPES["car-park"]["cid"] = true;
$SVG_LAYER_TYPES["car-park"]["w2g"] = "CP";
$SVG_LAYER_TYPES["car-park"]["fill"] = "none";
$SVG_LAYER_TYPES["car-park"]["cat_name"] = "Car Parks";

//building outline
$SVG_LAYER_TYPES["outline-w2g"] = array();
$SVG_LAYER_TYPES["outline-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["outline-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["outline-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["outline-w2g"]["w2g"] = "BO";
$SVG_LAYER_TYPES["outline-w2g"]["fill"] = "#FFFFE0";
$SVG_LAYER_TYPES["outline-w2g"]["cat_name"] = "Outline";

//corridors
$SVG_LAYER_TYPES["corridors-w2g"] = array();
$SVG_LAYER_TYPES["corridors-w2g"]["vertex"] = false;
$SVG_LAYER_TYPES["corridors-w2g"]["layer"] = false;
$SVG_LAYER_TYPES["corridors-w2g"]["cid"] = true;
$SVG_LAYER_TYPES["corridors-w2g"]["w2g"] = "CO";
$SVG_LAYER_TYPES["corridors-w2g"]["fill"] = "#99cccc";
$SVG_LAYER_TYPES["corridors-w2g"]["cat_name"] = "Corridors";
$SVG_LAYER_TYPES["corridors-w2g"]["printOrder"] = 1;

//     shopping => "SP",
$SVG_LAYER_TYPES["shopping"] = array();
$SVG_LAYER_TYPES["shopping"]["vertex"] = false;
$SVG_LAYER_TYPES["shopping"]["layer"] = false;
$SVG_LAYER_TYPES["shopping"]["cid"] = true;
$SVG_LAYER_TYPES["shopping"]["w2g"] = "SP";
$SVG_LAYER_TYPES["shopping"]["fill"] = "none";
$SVG_LAYER_TYPES["shopping"]["cat_name"] = "Shops";
//     clothes-men => "CM",
$SVG_LAYER_TYPES["clothes-men"] = array();
$SVG_LAYER_TYPES["clothes-men"]["vertex"] = false;
$SVG_LAYER_TYPES["clothes-men"]["layer"] = false;
$SVG_LAYER_TYPES["clothes-men"]["cid"] = true;
$SVG_LAYER_TYPES["clothes-men"]["w2g"] = "CM";
$SVG_LAYER_TYPES["clothes-men"]["fill"] = "none";
$SVG_LAYER_TYPES["clothes-men"]["cat_name"] = "Clothes(Men)";
//     clothes-women => "CW",
$SVG_LAYER_TYPES["clothes-women"] = array();
$SVG_LAYER_TYPES["clothes-women"]["vertex"] = false;
$SVG_LAYER_TYPES["clothes-women"]["layer"] = false;
$SVG_LAYER_TYPES["clothes-women"]["cid"] = true;
$SVG_LAYER_TYPES["clothes-women"]["w2g"] = "CW";
$SVG_LAYER_TYPES["clothes-women"]["fill"] = "none";
$SVG_LAYER_TYPES["clothes-women"]["cat_name"] = "Clothes(Women)";
//     health-beauty => "HB",
$SVG_LAYER_TYPES["health-beauty"] = array();
$SVG_LAYER_TYPES["health-beauty"]["vertex"] = false;
$SVG_LAYER_TYPES["health-beauty"]["layer"] = false;
$SVG_LAYER_TYPES["health-beauty"]["cid"] = true;
$SVG_LAYER_TYPES["health-beauty"]["w2g"] = "HB";
$SVG_LAYER_TYPES["health-beauty"]["fill"] = "none";
$SVG_LAYER_TYPES["health-beauty"]["cat_name"] = "Health and Beauty";
//     bags => "BA",
$SVG_LAYER_TYPES["bags"] = array();
$SVG_LAYER_TYPES["bags"]["vertex"] = false;
$SVG_LAYER_TYPES["bags"]["layer"] = false;
$SVG_LAYER_TYPES["bags"]["cid"] = true;
$SVG_LAYER_TYPES["bags"]["w2g"] = "BA";
$SVG_LAYER_TYPES["bags"]["fill"] = "none";
$SVG_LAYER_TYPES["bags"]["cat_name"] = "Bags";
//     Jewellers => "JW",
$SVG_LAYER_TYPES["jewellers"] = array();
$SVG_LAYER_TYPES["jewellers"]["vertex"] = false;
$SVG_LAYER_TYPES["jewellers"]["layer"] = false;
$SVG_LAYER_TYPES["jewellers"]["cid"] = true;
$SVG_LAYER_TYPES["jewellers"]["w2g"] = "JW";
$SVG_LAYER_TYPES["jewellers"]["fill"] = "none";
$SVG_LAYER_TYPES["jewellers"]["cat_name"] = "Jewellers";
//     Supermarket => "SM",
$SVG_LAYER_TYPES["supermarket"] = array();
$SVG_LAYER_TYPES["supermarket"]["vertex"] = false;
$SVG_LAYER_TYPES["supermarket"]["layer"] = false;
$SVG_LAYER_TYPES["supermarket"]["cid"] = true;
$SVG_LAYER_TYPES["supermarket"]["w2g"] = "SM";
$SVG_LAYER_TYPES["supermarket"]["fill"] = "none";
$SVG_LAYER_TYPES["supermarket"]["cat_name"] = "Supermarket";
//     Home and Furniture => "HF",
$SVG_LAYER_TYPES["home-furniture"] = array();
$SVG_LAYER_TYPES["home-furniture"]["vertex"] = false;
$SVG_LAYER_TYPES["home-furniture"]["layer"] = false;
$SVG_LAYER_TYPES["home-furniture"]["cid"] = true;
$SVG_LAYER_TYPES["home-furniture"]["w2g"] = "HF";
$SVG_LAYER_TYPES["home-furniture"]["fill"] = "none";
$SVG_LAYER_TYPES["home-furniture"]["cat_name"] = "Home and Furniture";
//     Footwear => "FW",
$SVG_LAYER_TYPES["footwear"] = array();
$SVG_LAYER_TYPES["footwear"]["vertex"] = false;
$SVG_LAYER_TYPES["footwear"]["layer"] = false;
$SVG_LAYER_TYPES["footwear"]["cid"] = true;
$SVG_LAYER_TYPES["footwear"]["w2g"] = "FW";
$SVG_LAYER_TYPES["footwear"]["fill"] = "none";
$SVG_LAYER_TYPES["footwear"]["cat_name"] = "Footwear";
//     Electronics => "ET",
$SVG_LAYER_TYPES["electronics"] = array();
$SVG_LAYER_TYPES["electronics"]["vertex"] = false;
$SVG_LAYER_TYPES["electronics"]["layer"] = false;
$SVG_LAYER_TYPES["electronics"]["cid"] = true;
$SVG_LAYER_TYPES["electronics"]["w2g"] = "ET";
$SVG_LAYER_TYPES["electronics"]["fill"] = "none";
$SVG_LAYER_TYPES["electronics"]["cat_name"] = "Electronics";
//     Restaurant => "RT",
$SVG_LAYER_TYPES["restaurant"] = array();
$SVG_LAYER_TYPES["restaurant"]["vertex"] = false;
$SVG_LAYER_TYPES["restaurant"]["layer"] = false;
$SVG_LAYER_TYPES["restaurant"]["cid"] = true;
$SVG_LAYER_TYPES["restaurant"]["w2g"] = "RT";
$SVG_LAYER_TYPES["restaurant"]["fill"] = "none";
$SVG_LAYER_TYPES["restaurant"]["cat_name"] = "Restaurant";
//     Cafe => "CF",
$SVG_LAYER_TYPES["cafe"] = array();
$SVG_LAYER_TYPES["cafe"]["vertex"] = false;
$SVG_LAYER_TYPES["cafe"]["layer"] = false;
$SVG_LAYER_TYPES["cafe"]["cid"] = true;
$SVG_LAYER_TYPES["cafe"]["w2g"] = "CF";
$SVG_LAYER_TYPES["cafe"]["fill"] = "none";
$SVG_LAYER_TYPES["cafe"]["cat_name"] = "Cafe";
//     Cinema => "CN",
$SVG_LAYER_TYPES["cinema"] = array();
$SVG_LAYER_TYPES["cinema"]["vertex"] = false;
$SVG_LAYER_TYPES["cinema"]["layer"] = false;
$SVG_LAYER_TYPES["cinema"]["cid"] = true;
$SVG_LAYER_TYPES["cinema"]["w2g"] = "CN";
$SVG_LAYER_TYPES["cinema"]["fill"] = "none";
$SVG_LAYER_TYPES["cinema"]["cat_name"] = "Cinema";
//     KTV => "KTV",
$SVG_LAYER_TYPES["ktv"] = array();
$SVG_LAYER_TYPES["ktv"]["vertex"] = false;
$SVG_LAYER_TYPES["ktv"]["layer"] = false;
$SVG_LAYER_TYPES["ktv"]["cid"] = true;
$SVG_LAYER_TYPES["ktv"]["w2g"] = "KTV";
$SVG_LAYER_TYPES["ktv"]["fill"] = "none";
$SVG_LAYER_TYPES["ktv"]["cat_name"] = "KTV";
//     Subway Stations => "SW",
$SVG_LAYER_TYPES["subway-station"] = array();
$SVG_LAYER_TYPES["subway-station"]["vertex"] = false;
$SVG_LAYER_TYPES["subway-station"]["layer"] = false;
$SVG_LAYER_TYPES["subway-station"]["cid"] = true;
$SVG_LAYER_TYPES["subway-station"]["w2g"] = "SW";
$SVG_LAYER_TYPES["subway-station"]["fill"] = "none";
$SVG_LAYER_TYPES["subway-station"]["cat_name"] = "Subway Stations";
//     ATM => "ATM",
$SVG_LAYER_TYPES["atm"] = array();
$SVG_LAYER_TYPES["atm"]["vertex"] = false;
$SVG_LAYER_TYPES["atm"]["layer"] = false;
$SVG_LAYER_TYPES["atm"]["cid"] = true;
$SVG_LAYER_TYPES["atm"]["w2g"] = "ATM";
$SVG_LAYER_TYPES["atm"]["fill"] = "none";
$SVG_LAYER_TYPES["atm"]["cat_name"] = "ATM";
//     Entertainment => "ET",
$SVG_LAYER_TYPES["entertainment"] = array();
$SVG_LAYER_TYPES["entertainment"]["vertex"] = false;
$SVG_LAYER_TYPES["entertainment"]["layer"] = false;
$SVG_LAYER_TYPES["entertainment"]["cid"] = true;
$SVG_LAYER_TYPES["entertainment"]["w2g"] = "ET";
$SVG_LAYER_TYPES["entertainment"]["fill"] = "none";
$SVG_LAYER_TYPES["entertainment"]["cat_name"] = "Entertainment";
//     Transport => "TP",
$SVG_LAYER_TYPES["transport"] = array();
$SVG_LAYER_TYPES["transport"]["vertex"] = false;
$SVG_LAYER_TYPES["transport"]["layer"] = false;
$SVG_LAYER_TYPES["transport"]["cid"] = true;
$SVG_LAYER_TYPES["transport"]["w2g"] = "TP";
$SVG_LAYER_TYPES["transport"]["fill"] = "none";
$SVG_LAYER_TYPES["transport"]["cat_name"] = "Transport";
//     Toilets => "TL",
$SVG_LAYER_TYPES["toilet"] = array();
$SVG_LAYER_TYPES["toilet"]["vertex"] = false;
$SVG_LAYER_TYPES["toilet"]["layer"] = false;
$SVG_LAYER_TYPES["toilet"]["cid"] = true;
$SVG_LAYER_TYPES["toilet"]["w2g"] = "TL";
$SVG_LAYER_TYPES["toilet"]["fill"] = "none";
$SVG_LAYER_TYPES["toilet"]["cat_name"] = "Toilets";


function isCIDw2gType($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    return $slt["cid"];
    
  }
  
  // Default behaviour
  return false;
}

function isVertexW2GType($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    return $slt["vertex"];
    
  }
  
  // Default behaviour
  return false;
}


function getFillFromW2Gtype($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    if (!isset($slt["fill"])) return "";
    return $slt["fill"];
    
  }
  
  // Default behaviour
  return "";
}

function getLinkFromW2GType($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    if (!isset($slt["link"])) return "";
    return $slt["link"];
    
  }
  
  // Default behaviour
  return "";
}

function getCatNameFromW2GType($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    if (!isset($slt["cat_name"])) return "";
    return $slt["cat_name"];
    
  }
  
  // Default behaviour
  return "";
}


function isLayerw2gType($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    return $slt["layer"];
    
  }
  
  // Default behaviour
  return false;
}

function getLayerNameForw2gType($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    return $slt["layer_name"];
    
  }
  
  // Default behaviour
  return "";
}

function getPrintOrderForw2gType($w2gType){
  global $SVG_LAYER_TYPES;
  foreach ($SVG_LAYER_TYPES as $slt){
    if ($slt["w2g"]!=$w2gType) continue;
    
    if (!isset($slt["printOrder"])) return false;
    return $slt["printOrder"];
    
  }
  
  // Default behaviour
  return false;
}

/// Set output directory
// Default value... 
$PROJECT_DIR=$PROC_DIR;
if (isset($_REQUEST["project_dir"])){
  $PROJECT_DIR=$_REQUEST["project_dir"];
}

/// Printing
// Print function
function prt($w){global $EoL; echo "$w$EoL";}
function prtE($w){global $EoL; echo "$EoL*** ERROR: $w ***$EoL$EoL";} //doesn't print properly to php window - no EoL
function prtW($w){global $EoL; echo "WARNING: $w ***$EoL";} 
function prtSec($w){global $EoL; echo "$EoL* $w$EoL";}
function prtSubSec($w,$t=1){
  global $EoL,$TAB; 
  for ($i=0; $i<$t; $i++) echo $TAB;
  echo "- $w$EoL"; //need this to print to php properly
}

//Include project specific constants

/// Handle File names
$fname = $_REQUEST["filename"];
$fbname=basename($fname,".svg");

/// Get Building and Floor
$p = explode("_", $fbname);
if (count($p)<3){
  prtE("Wrong filename format: projectName_Building_Floor");
  return;
}
$project = $p[0];

$settings = $DIR."/settings/$project.php";

if (file_exists($settings)) {
	include_once ($settings);	
}



?>
