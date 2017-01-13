<?php
include_once('simple_html_dom.php');
include_once('importconsts.php');

function install() {
GLOBAL $DIR,$BF_DIR,$fbname,$SNAP_EXT,$FINAL_EXT,$project,$building,$floor,$room,$SAPI;
/// Installing
  prtSec("INSTALLING PROJECT");
  prtSec("Creating SVG");
  $tmp_file_name = $BF_DIR."/".$fbname.$SNAP_EXT;
  $result = shell_exec("php $DIR/db_to_svg.php filename=$tmp_file_name project=$project building=$building floor=$floor room=$room sapi=".$SAPI);
  echo "$result"; 
  prtSec("Tiling SVG");
  $tmp_file_name = $BF_DIR."/".$fbname.$FINAL_EXT;
  $result = shell_exec("php $DIR/tile_svg.php filename=$tmp_file_name sapi=".$SAPI);
  echo "$result"; 
};

/// Print commands
$PCMD=true;

prtSec("PREPARING PROJECT ENVIRONMENT");
prtSubSec("Floorplan filename: '".$_REQUEST["filename"]."'");
if (!isset($_REQUEST["filename"])){
  prtE("filename parameter is needed");
  return;
}

//put this in description field of floor table
$description= $_REQUEST["description"];

$floor_type =0;
//put this in floor type- if not set then default = not the default floor
if (isset($_REQUEST["floor_type"])){
	$floor_type = $_REQUEST["floor_type"];	
}

$update = 0;
//True if floorplan to be updated rather than created
if (isset($_REQUEST["update"])){
	$update = $_REQUEST["update"];	
}
 
// All stages
$stage=10;
if (isset($_REQUEST["stage"])) 
  $stage=$_REQUEST["stage"];


$DIR=dirname(__FILE__);

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
$building = $p[1];
$floor = $p[2];
$room = "";
if (count($p) == 4) $room = $p[3];


// ------------ CREATE PRELIM (Folders and Copy files...) -------------------------

/// Create SCAN arguments
$SCAN_ARGS="project=$project building=$building floor=$floor room=$room";
if (isset($_REQUEST["scanargs"]))
  $SCAN_ARGS.=$_REQUEST["scanargs"];

/// Create project directory
$PROJECT_DIR=$PROC_DIR."/".$project."/";

prtSubSec("Project directory: ". $PROJECT_DIR);
if (!is_dir($PROJECT_DIR)) mkdir($PROJECT_DIR);
$BF_DIR=$PROJECT_DIR."/".$building.$floor.$room;

prtSubSec("Floorplan directory: ".$BF_DIR);
if (!is_dir($BF_DIR)) mkdir($BF_DIR);

$result = "";
$SAPI = php_sapi_name();
prtSec("Using SAPI=$SAPI");

//bypass all of this import part - only SVG and tile
if (isset($_REQUEST["installonly"]) && $_REQUEST["installonly"]=="true") {
$result = install();
prtSec("Completed");
return;
}

prtSubSec("Moving floorplan to project environment");
// Check Exists
if (!file_exists("$UPLOAD_DIR/$fname")){
  prtE("No Such File: $UPLOAD_DIR/$fname");
  return;
}
copy("$UPLOAD_DIR/$fname", "$BF_DIR/$fname");


prtSec("PROCESSING VECTOR OBJECTS");
$PCMD && prtSubSec("php $DIR/paths2poly.php filename=$BF_DIR/$fname project_dir=$BF_DIR sapi=".$SAPI);
$result = shell_exec("php $DIR/paths2poly.php filename=$BF_DIR/$fname project_dir=$BF_DIR sapi=".$SAPI);
echo "$result";

prtSec("CREATING W2G FILE");
// File after p2p
$tmp_file_name = $BF_DIR."/".$fbname.$P2P_EXT;
$PCMD && prtSubSec("php $DIR/scansvg.php $SCAN_ARGS filename=$tmp_file_name project_dir=$PROJECT_DIR sapi=".$SAPI);
$result = shell_exec("php $DIR/scansvg.php $SCAN_ARGS filename=$tmp_file_name project_dir=$PROJECT_DIR sapi=".$SAPI);
echo "$result";

prtSec("PROCESSING LOCATION OBJECTS");
// File after p2p
$tmp_file_name = $BF_DIR."/".$fbname.$SCAN_EXT;
$PCMD && prtSubSec("php $DIR/geofence.php filename=$tmp_file_name project=$project building=$building floor=$floor room=$room sapi=".$SAPI);
$result = shell_exec("php $DIR/geofence.php filename=$tmp_file_name project=$project building=$building floor=$floor room=$room sapi=".$SAPI);
echo "$result";

prtSec("CREATING ROUTING NETWORK");
// File after W2G
$tmp_file_name = $BF_DIR."/".$fbname.$GEO_EXT;
$PCMD && prtSubSec("php $DIR/snap2guiderails.php filename=$tmp_file_name project=$project building=$building floor=$floor room=$room sapi=".$SAPI);
$result = shell_exec("php $DIR/snap2guiderails.php filename=$tmp_file_name project=$project building=$building floor=$floor room=$room sapi=".$SAPI);
echo "$result";


/// Importing
prtSec("IMPORTING INTO DATABASE");
$tmp_file_name = $BF_DIR."/".$fbname.$SNAP_EXT;
$PCMD && prtSubSec("php $DIR/w2g2db.php filename=$tmp_file_name project=$project building=$building floor=$floor floor_type=$floor_type update=$update sapi=".$SAPI);
$result = shell_exec("php $DIR/w2g2db.php filename=$tmp_file_name project=$project building=$building floor=$floor room=$room floor_type=$floor_type description=$description update=$update sapi=".$SAPI);
echo "$result";

if (isset($_REQUEST["install"]) && $_REQUEST["install"]=="true"){
$result = install();
}

?>
