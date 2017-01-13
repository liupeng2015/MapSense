<?php
include_once('simple_html_dom.php');
include_once('importconsts.php');

/// Print commands
$PCMD=true;

prtSec("Tiling: '".$_REQUEST["filename"]."'");
if (!isset($_REQUEST["filename"])){
  prtE("filename parameter is needed");
  return;
}

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
if (count($p) ==4) $room = $p[3];

/// Tile
$result = shell_exec("./TileTools/tile_svg.sh $project $building $floor $room");
//$PCMD && prtSubSec("./TileTools/tile_svg.sh $project $building $floor $room");
echo "$result $EoL";
echo "Now installing....";
/// Install
//$result = shell_exec("./TileTools/install_tiles.sh $project $building $floor $INSTALL_DIR $room");
//$PCMD && prtSubSec("./TileTools/install_tiles.sh $project $building $floor $INSTALL_DIR");
echo "$result $EoL";

?>
