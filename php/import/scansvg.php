<?php
include_once('importconsts.php');
include_once('simple_html_dom.php');
include_once('libsvg.php');



//example usescript use: svg_scan.sh Colchester MB_G.svg 0 0 0 MB G 1 4096
//didn't call "project", "config" to save confusing with global variable config


/// CHECKs
if (!isset($_REQUEST["project"])){
    prtE( "Please enter project name");
    return;	 	  
}
if (!isset($_REQUEST["filename"])){
    prtE( "Please enter filename");
    return;	 	  
}
//these are necessary - return if not passed
if (!isset($_REQUEST["building"])){
    prtE( "Please enter project building");
    return;	 	  
}
if (!isset($_REQUEST["floor"])){
    prtE( "Please enter project floor");
    return;	 	  
}


$project = $_REQUEST["project"];
$fname = $_REQUEST["filename"];
$building = $_REQUEST["building"];
$floor = $_REQUEST["floor"];
$room = $_REQUEST["room"];
$PROJECT_DIR=$PROC_DIR."/".$project."/";



// Final PNG width/height
$img_width = $IMG_DIM;
if (isset($_REQUEST["img_width"]))
  $img_width = $_REQUEST["img_width"];
    

//tile and create png/jpg images - 1 = tile, 0 = don't
if (isset($_REQUEST["create_tiles"]))
     $create_tiles = $_REQUEST["create_tiles"];


// Load file - Create a DOM object from a file
prtSubSec("Loading");
$doc = file_get_html($_REQUEST['filename'],$use_include_path = false, $context=null, 
			$offset = -1, $maxLen=-1, $lowercase = true, $forceTagsClosed=true, 
			$target_charset = DEFAULT_TARGET_CHARSET, $stripRN=false, 
			$defaultBRText=DEFAULT_BR_TEXT);
if ($doc===FALSE){
  prtE("Unable to load file ".$_REQUEST['filename']);
  return;
}

prtSubSec("Checking SVG");
$svg=$doc->find('svg',0);
if ($svg==null) {
  prtE($_REQUEST['filename']." is not an SVG file!");
  return;
}

// Create the w2g file
$fbname=basename($fname,"_p2p.svg");
$w2g_file=$PROJECT_DIR."/".$building.$floor.$room."/".$fbname.".w2g";
$fh = fopen($w2g_file, "w");
if($fh==false){
    prtE("Unable to create file: ".$w2g_file);
    return;
}



/// DO THE AWK JOB --------------------------------------------------------------------------

// Save Project
fputs($fh, "p;".$project.";".$building.";".$floor.";\n");

// ViewBox - IMG PPM Scale
prtSubSec("PPM Scale");
$ppm=getPPMInfo($svg,$building);
if (!$ppm["found"]){
  prtE("NO PPM SCALE... DEFAULT PNG DIMENSIONS WILL BE USED (non-fatal)");
}

$img_dim=getVBPngDim($svg, $img_width, $ppm);
if (($img_dim["swo"] != "0") || ($img_dim["sho"] != "0")){
	prtE("Viewbox offset not zero: w offset=".$img_dim["swo"].", h offset=".$img_dim["sho"]);
	
}
fputs($fh, "vb;".$img_dim["sw"].";".$img_dim["sh"].";".$img_dim["w"].";".$img_dim["h"].";\n");
if ($ppm["found"])
  fputs($fh, $ppm["w2gstr"]);


// Generalback layer - make generalback layer first so that it is behind all other layers
prtSubSec("Generalback Layer");
w2g_generalback($fh, $svg);  


// Room text
prtSubSec("Room Text");
w2g_room_text($fh, $svg);

// GuideRails to vertices
prtSubSec("GuideRails");
w2g_guiderails($fh, $svg);

// Initialize vertices array... 
prtSubSec("Layers");
// layers like doors use this
$vertices=array();
// Layers
w2g_layers($fh, $svg);

// Go for the vertices,edges
prtSubSec("Paths");
$edges=array();
w2g_paths($fh, $svg);

// Has to be before Locations 
// for geof later
prtSubSec("Getting Aliases");
w2g_aliases($fh, $svg);

// This is the building perimeter 
prtSubSec("Perimeter Layer");
w2g_perimeter($fh, $svg);

// locations
prtSubSec("Locations!");
w2g_locations($fh, $svg);

// General layer - make general layer last so that it is not hidden by other layers
prtSubSec("General Layer");
w2g_general($fh, $svg);
/// Eo AWK JOB 


?>