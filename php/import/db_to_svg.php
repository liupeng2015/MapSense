<?php
$new_paths = "..".PATH_SEPARATOR."../db";
set_include_path(get_include_path() . PATH_SEPARATOR . $new_paths);
include_once("mymapslib.php");
include_once('importconsts.php');
include_once('Tools.php');
include_once('MathTools.php');

//require_once('PhpConsole.php');
//PhpConsole::start();

error_reporting(E_ERROR);
$x_scale  = 1; 
$y_scale  = 1;
$x_offset = 0;
$y_offset = 0;
 

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

$project = $_REQUEST["project"];
$fname = $_REQUEST["filename"];
$req_b = $_REQUEST["building"];
$req_f = $_REQUEST["floor"];
$req_r = $_REQUEST["room"];
$block_general = 0;
$block_general = $_REQUEST["block_general"];

//if $admin == 1, verticies, edges and overlay drawn in db2svg routine
if (isset($_REQUEST["admin"])){
	$admin = $_REQUEST["admin"];
}
else {
	$admin = 0; //default is normal mode with no verticies and edges
}

$PROJECT_DIR=$PROC_DIR."/".$project."/".$req_b.$req_f.$req_r."/";
echo $PROJECT_DIR;
// Setup files
$fbname=basename($fname,$SNAP_EXT);

$svg_file = $PROJECT_DIR.$fbname."_final.svg";
if ($admin ==1) $svg_file = $PROJECT_DIR.$fbname."_final_admin.svg";

//use w2g file for extracting the general layer
$w2g_file = $PROJECT_DIR.$fbname.".w2g";

prtSubSec( "Opening W2G file...".$w2g_file);
prtSubSec( "Opening database...");

// Connect

$conf=$CONF_DIR."/".$project."_db.conf";
$connection = new connection($conf);
$connection->connect();
$GLOBALS["connection"] = $connection;

// Check we are connected
if (!$connection->isConnected()) {
	prtE('Could not connect: ' . $connection->last_error);
	return;
}

//Open up file 
prtSubSec("Creating new SVG file...");

	$svg_file_handle = fopen($svg_file, "w");
	$floor = new floors();
	$loc = new locations();
	
	$line_op_overlay = ""; //used to store overlay elements for room highlight - must be written last to SVG	
	//$edge = new edges();
//	$vertex = new vertex();

	prtSubSec ("Writing project: ".$project." Building: ".$req_b." Floor: ".$req_f." Room: ".$req_r);

$very_last_text =0;	
$last_general = 0;
$found_scale = 0; //flag to save reading whole file, but just to end of "vb" to get scale parameters
$list = getList_floors();
//echo "Req building: ".$req_b." Required floor".$req_f." Required room = ".$req_r.$EoL;


foreach ($list as $f) {
	//	echo $f->building.",".$f->floor.$EoL;
		if (($f->floor == $req_f) && ($f->building == $req_b) && ($f->room == $req_r)) {
		prtSubSec ("Match found....bf = ".$f->id);
		$bf=$f->id;
		$ppm=$f->ppm;
		prtSubSec ("PPM = ".$ppm);
		continue;
		}
};


//Header information for W2G file to get SVG size


	//Project details
	$w2g_file_handle = fopen($w2g_file, "r");
	
	// Start Parsing
	while (!feof($w2g_file_handle)) {
        $line = fgets($w2g_file_handle);
        $parts = explode(';', $line);
        if (trim($line) == '') continue;
		switch ($parts[0]) {
			case "vb":
				//Get viewbox dimenensions
				$width = $parts[1];
				$height = $parts[2];
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
				prtSubSec ("Closest ^2: X = ".$pow2x." Y= ".$pow2y);
				prtSubSec ("Using ^2 = ".$maxpow);
				
				prtSubSec ("Offset: X = ".$x_offset." Y= ".$y_offset);
				
		}
	};
	fclose($w2g_file_handle);
	
	
	
	prtSubSec( "SVG viewbox: ".$width."x".$height."\n");
	
	
				$line_op = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
				$line_op .= "<?xml-stylesheet type=\"text/css\" href=\"http://www.wai2go.co.uk/RL3/css_svg/no_paths.css\" title = \"nopaths\"?>\n";
				$line_op .= "<?xml-stylesheet type=\"text/css\" href=\"http://www.wai2go.co.uk/RL3/css_svg/paths.css\"  title=\"paths\" alternate=\"yes\" ?>\n";
				$line_op .= "<?xml-stylesheet type=\"text/css\" href=\"http://www.wai2go.co.uk/RL3/css_svg/doors.css\" title = \"doors\"?>\n";
				$line_op .= "<?xml-stylesheet type=\"text/css\" href=\"http://www.wai2go.co.uk/RL3/css_svg/no_doors.css\" title=\"no_doors\" alternate=\"yes\" ?>\n";
				$line_op .= "<?xml-stylesheet type=\"text/css\" href=\"http://www.wai2go.co.uk/RL3/css_svg/locations.css\" title = \"locations\"?>\n";
				$line_op .= "<?xml-stylesheet type=\"text/css\" href=\"http://www.wai2go.co.uk/RL3/css_svg/no_locations.css\" title=\"no_locations\" alternate=\"yes\" ?>\n";
				$line_op .= "<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n";
				$line_op .= "<svg version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" x=\"0px\" y=\"0px\"\n";
				$line_op .= "width=\"100%\" height=\"100%\" viewBox=\"0 0 ".$width." ".$height."\" enable-background=\"new 0 0 ".$width." ".$height."\"\n";
				$line_op .= "xml:space=\"preserve\">\n";
// KG: uncommented:
//				if ($admin == 1) {
					$line_op .= "<defs>\n";
					//$line_op .= "<script  xlink:href=\"http://www.wai2go.co.uk/RL3/lib_svg/js/click_svg_element.js\"/>\n";
					$line_op .= "<script  xlink:href=\"http://www.wai2go.co.uk/RL3/lib_svg/js/jquery-1.7.1.min.js\"/>\n";
					$line_op .= "<script  xlink:href=\"http://www.wai2go.co.uk/RL3/lib_svg/js/SVGPan_admin.js\"/>\n";
				/*	$line_op .= "<style type=\"text/css\"><![CDATA[\n";
					$line_op .= "line.paths {display: none}\n";
					$line_op .= "]]></style>\n";
					*/
					//drop shadow definition
					//$line_op .= "<filter id=\"drop-shadow\" width = \"10px\" height=\"10px\">\n";
					//$line_op .= "<feGaussianBlur in=\"SourceGraphic\" result= \"blur-out\" stdDeviation=\"0.5\"/>\n";
					//$line_op .= "<feOffset in = \"blur-out\" result = \"the-shadow\" dx=\"0.7\" dy=\"0.7\"/>\n"; 
					
					//$line_op .= "<feMerge>\n"; 
					//$line_op .= "<feMergeNode/>\n";
					//$line_op .= "<feBlend in=\"SourceGraphic\" in2=\"the-shadow\" mode = \"normal\"/>\n"; 
					//$line_op .= "</feBlend>\n";					
					//$line_op .= "</filter>\n";
					$line_op .= "</defs>\n";
	//			}
				$line_op .= "<g id = \"viewport\">\n";
				
				//$line_op .= "<polygon fill=\"".$background_fill."\" stroke=\"".$background_colour."\" points=\"0,0 0,".$height." ".$width.",".$height." ".$width.",0\" stroke-width=\"0.3\"/>\n";			
				fputs ($svg_file_handle, $line_op);
	
	$line_op= "";

	//perimeter layer - this is to be drawn first so is on bottom - any stuff to be drawn on the bottom is put here	
	
	$w2g_file_handle = fopen($w2g_file, "r");
	
	// Start Parsing
	//Look for "generalback" layer - this is drawn first 
	$general_layers = 0; //flag to indicate general layers within W2G file
	while (!feof($w2g_file_handle)) {
        $line = fgets($w2g_file_handle);
        $parts = explode(';', $line);
		if (trim($line) == '') continue;
		//if general layers in the following format (used for adding general layers after import) - block_general =1 
		//G1;
		//svg stuff
		//G1;
		if ($block_general ==1) {
		
			if ($general_layers == 1) {
			//echo "writing".$line;
			fputs ($svg_file_handle, $line); //write raw SVG from W2G file to new SVG
			};
			if ($parts[0] == "G1") {
				if ($general_layers == 0) $general_layers = 1;  //opening G1 tag
				else $general_layers = 0; //closing G1 tag
				echo "Match: gl= ".$general_layers;
			};	
		}
		else 
		//general layer is in this format:
		//G1;svg stuff
		//G1;svg stuff
		{
			if ($parts[0] == "G1") fputs ($svg_file_handle, $parts[1]); //write raw SVG from W2G file to new SVG after "G1" tag		
		};	
	};
	
	fclose($w2g_file_handle);
	prtSubSec( "Background read and created\n");

//Draw location polynomials, text and doors	
		
	$loc = getList_locations("bf=$bf");

//Order of drawing layers is: generalback,perimeter,locations,general
//Now do the perimeter of the floorplan
//temp
$line_op_doors = "";
$line_op_entrances = "";
$line_op_corridors = "";
$line_op_entrances = "";
$line_op_text = "";
$line_op_perimeters = "";
$line_op_locations = "";



	foreach ($loc as $l) {
		$svg = new svg_info();
		$lid = $l->id;
		$svg = getSVGInfo("lid='$lid'", "");
		$loc_cid = $l->cid;
		foreach ($svg as $s) {
			switch ($s['type']) {
				case "L":			
					$l_points = $s['points'];	//polypoints are taken from the svg_info table rather than locations
					//perimeter polypoints are not saved in locations table....only in svg_info
					$scaled_polypoints = ""; 
					$points = explode("\n",$l_points);
					foreach ($points as $each_point) {
						$Px_y = explode(",", $each_point);
						$Px= floatval($Px_y[0]);
						$Py= floatval($Px_y[1]);
						//echo "Px =".$Px." Py =".$Py;
						//echo "x_scale = ".$x_scale.", y_scale = ".$y_scale;
						if (($Px != 0) && ($Py != 0)) {
						$svg_x = (($Px-$x_offset)/$x_scale);
						$svg_y = (($Py-$y_offset)/$y_scale);
						if ($svg_x >$width) $svg_x = $width;
						if ($svg_y >$height) $svg_y = $height;
						if ($svg_x <0) $svg_x = 0;
						if ($svg_y <0) $svg_y = 0;
						$scaled_polypoints .= $svg_x.",".$svg_y." "; //exclude any "0" points
						}
					
					}
					//$line_op = "l;".$lid.";".$l->name.";".(($l->lat-$x_offset)/$x_scale).";".(($l->lon-$y_offset)/$y_scale).";".$scaled_polypoints.";".$s['fill'].";".$s['color'].";".$s['stroke'].";\n";
					//original without drop shadow
					
					//Need a better way of doing this - hard-wired
					if ($loc_cid == "65") {
						prtSubSec( "Perimeter layer read and created\n");
						$line_op_perimeters .= "<polygon id = \"L".$lid."\" class = \"perimeter\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".($ppm*$perimeter_width/$x_scale)."\" points=\"".$scaled_polypoints."\"/>\n";
						//create overlay - make background overlay straight so can interact with from SVG floorplan editor
					//	$line_op_overlay .= "<polygon id = \"O".$lid."\" class = \"Perimeter_overlay\" display=\"inline\" fill-opacity=\"0.001\" fill=\"#FFFFFF\" stroke=\"none\" points=\"".$scaled_polypoints."\"/>\n";
						//fputs ($svg_file_handle, $line_op);
					}
					//cat 63 = lobby, atriums and foyers - could make the same as corridors (cat == 64) eventually
					if ( ($loc_cid == "64")) {
					$line_op_corridors .= "<polygon id = \"L".$lid."\" class = \"location\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['fill']."\" stroke-width=\"".($ppm*$s['stroke']/$x_scale)."\" points=\"".$scaled_polypoints."\"/>\n";
					$line_op_overlay .= "<polygon id = \"O".$lid."\" class = \"overlay\" display=\"inline\" fill-opacity=\"0.001\" fill=\"#FFFFFF\" stroke=\"none\" points=\"".$scaled_polypoints."\"/>\n";

					}
					
					if ( ($loc_cid == "63")) {
					$line_op_corridors .= "<polygon id = \"L".$lid."\" class = \"location\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".($ppm*$s['stroke']/$x_scale)."\" points=\"".$scaled_polypoints."\"/>\n";
					$line_op_overlay .= "<polygon id = \"O".$lid."\" class = \"overlay\" display=\"inline\" fill-opacity=\"0.001\" fill=\"#FFFFFF\" stroke=\"none\" points=\"".$scaled_polypoints."\"/>\n";

					}					
					
					//fputs ($svg_file_handle, $line_op);
					
					
					
					//with shadow
					//$line_op= "<polygon id = \"L".$lid."\" style=\"filter:url(#drop-shadow)\" class = \"location\" display=\"inline\" fill=\"none\" stroke=\"#000000\" stroke-width=\"0.8\" points=\"".$scaled_polypoints."\"/>\n";
						
		
					break;						
			}
		}					
	
	}
	
	
	foreach ($loc as $l) {
		//prtSubSec ("<-----$l->name------->");
		//Now search through SVG_info table for extra information
		$svg = new svg_info();
		$lid = $l->id;
		$svg = getSVGInfo("lid='$lid'", "");
		$loc_cid = $l->cid;
			
		
		foreach ($svg as $s) {
			switch ($s['type']) {
				case "L":			
		
					$l_points = $l->polygon;	
					$scaled_polypoints = ""; 
					$points = explode("\n",$l_points);
					foreach ($points as $each_point) {
						$Px_y = explode(",", $each_point);
						$Px= floatval($Px_y[0]);
						$Py= floatval($Px_y[1]);
						//echo "Px =".$Px." Py =".$Py;
						//echo "x_scale = ".$x_scale.", y_scale = ".$y_scale;
						if (($Px != 0) && ($Py != 0)) $scaled_polypoints .= (($Px-$x_offset)/$x_scale).",".(($Py-$y_offset)/$y_scale)." "; //exclude any "0" points
					}
					//$line_op = "l;".$lid.";".$l->name.";".(($l->lat-$x_offset)/$x_scale).";".(($l->lon-$y_offset)/$y_scale).";".$scaled_polypoints.";".$s['fill'].";".$s['color'].";".$s['stroke'].";\n";
					//original without drop shadow
					
					//Need a better way of doing this - hard-wired
					if ($loc_cid == "65") continue;  //don't draw building outline - this is drawn first above
					
					if (($loc_cid !="64") && ($loc_cid !="63") )
					
					$line_op_locations .= "<polygon id = \"L".$lid."\" class = \"location\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".($ppm*$s['stroke']/$x_scale)."\" points=\"".$scaled_polypoints."\"/>\n";
					
					//***********************
					$polygon_fill = $s['fill'];
					//$polygon_colour = $s['color'];
			//		$polygon_stroke = $s['stroke'];
					
					
					
					
					//with shadow
					//$line_op= "<polygon id = \"L".$lid."\" style=\"filter:url(#drop-shadow)\" class = \"location\" display=\"inline\" fill=\"none\" stroke=\"#000000\" stroke-width=\"0.8\" points=\"".$scaled_polypoints."\"/>\n";
					
					//fputs ($svg_file_handle, $line_op);
					//fputs ($w2g_file_handle, $line_op);
					//prtSubSec($line_op);
					//create overlay, but write to file last so it sits on top of the SVG for hover/select
					
					$line_op_overlay .= "<polygon id = \"O".$lid."\" class = \"overlay\" display=\"inline\" fill-opacity=\"0.001\" fill=\"#FFFFFF\" stroke=\"none\" points=\"".$scaled_polypoints."\"/>\n";
					
					break;		
				case "T":	
					//t;45;0.9941;-0.1089;0.1089;0.9941;775.4922;504.7583;5NW.7.5;2.25;7;0
					//$line_op = "t;".$lid."_".$s['index'].";".$transform.";".$x.";".$y.";".$s['value'].";".$s['fontsize'].";".$s['color'].";".$s['fill']."\n"; 
					//fputs ($w2g_file_handle, $line_op);
					$name = $s['value']; //escape the & and ' since svg doesn't like these
					$name1 = str_replace("&","&amp;",$name);
					//$name = str_replace($name,"'","&quot;");
  
					
					$line_op_text .= "<text id = \"T".($lid)."_".$s['index']."\" class = \"text\" transform=\"matrix(".$s['transform'].")\" font-family=\"'Calibri'\" fill = \"".$s['fill']."\" font-size=\"".$s['fontsize']."\">".$name1."</text>\n";
					//fputs ($svg_file_handle, $line_op);
					break;
				case "D":
					//transform in the form: 822.9471 735.0195,270 (rotation x y);
					$transform = $s['transform'];
					$t_comp = explode(" ", $transform);
					$coord_part = explode(",", $t_comp[1]);
					$x = $coord_part[0];
					$y = $coord_part[1];

					// transform ="rotate(353.74838577062 789.31356,457.89034)" (rot x,y)
					//v;149;815.4884;549.90064;D;6;268.11470657121;;-0.9995 0.0329 -0.0329 -0.9995 815.4884 549.90064 
					//The door refers to lid +1
					//$line_op = "<polygon id = \"D".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".$s['stroke']."\" points=\"".$x.",".($y-$door_size)." ".($x+$door_size).",".($y+$door_size)." ".($x-$door_size).",".($y+$door_size)."\"/>\n";
					//Triangle
					//original if ($door_type == 0) $line_op = "<polygon id = \"D".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$door_fill."\" stroke=\"".$door_color."\" stroke-width=\"".($door_stroke/$x_scale)."\" points=\"".$x.",".( ($y-2*($door_size/$y_scale))-(($door_stroke/$y_scale)/2) )." ".($x+$door_size/$x_scale).",".($y-(($door_stroke/$y_scale)/2))." ".($x).",".($y-($door_stroke/$y_scale)/2)." ".($x-($door_size/$x_scale)).",".($y-(($door_stroke/$y_scale)/2))."\"/>\n";
					if ($door_type == 0) $line_op_doors .= "<polygon id = \"D".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$door_fill."\" stroke=\"".$door_color."\" stroke-width=\"".($ppm*$door_stroke/$x_scale)."\" points=\"".$x.",".($y-($ppm*$door_size/$y_scale)-(0.5*($ppm*$door_stroke/$x_scale)))." ".($x-(0.5*($ppm*$door_size/$x_scale))).",".($y-(0.5*($ppm*$door_stroke/$x_scale)))." ".($x+(0.5*($ppm*$door_size/$x_scale))).",".($y-(0.5*($ppm*$door_stroke/$x_scale)))."\"/>\n";
					
					//CAD style door - normal
					if ($door_type ==1) {
						$scaled_door_size = $ppm*$door_size/$x_scale;
						$scaled_gap_around_rooms=$ppm*$gap_around_rooms/$x_scale;
							
							
					 // $line_op = "<line transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"none\" stroke=\"".$polygon_stroke."\" stroke-width=\"".(($ppm*$gap_around_rooms/$x_scale))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\" stroke-miterlimit=\"10\" x1=\"".($x-(8/$x_scale))."\" y1=\"".($y-($polygon_stroke_w/4))."\" x2=\"".($x-(8/$x_scale))."\" y2=\"".($y-(16/$x_scale))."\"/>";
					 // $line_op .= "<path transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"none\" stroke=\"".$polygon_stroke."\" stroke-width=\"".(($ppm*$gap_around_rooms/$x_scale))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  stroke-dasharray='0.1,0.8' d=\"M".($x-(8/$x_scale)).",".($y-(($polygon_stroke_w/4)+(16/$y_scale)))."c".(6.628/$x_scale).",0,".(16/$x_scale).",".(5.373/$x_scale).",".(16/$x_scale).",".(16/$x_scale)."\"/>";
			
					   $line_op_doors .= "<line transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"".$polygon_fill."\" stroke=\"".$polygon_fill."\" stroke-width=\"".($scaled_gap_around_rooms+($scaled_gap_around_rooms*0.4))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  x1=\"".($x-($scaled_door_size/2)+($scaled_gap_around_rooms))."\" y1=\"".($y-($scaled_gap_around_rooms*0.2))."\" x2=\"".($x+($scaled_door_size/2)-($scaled_gap_around_rooms))."\" y2=\"".($y-($scaled_gap_around_rooms*0.2))."\"/>";
					   $line_op_doors .= "<line transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"none\" stroke=\"".$polygon_stroke."\" stroke-width=\"".(($ppm*$gap_around_rooms/$x_scale))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\" stroke-miterlimit=\"10\" x1=\"".($x-$scaled_door_size/2)."\" y1=\"".($y)."\" x2=\"".($x-$scaled_door_size/2)."\" y2=\"".(($y-$scaled_door_size)+($scaled_gap_around_rooms/4))."\"/>";
					 //$line_op_doors .= "<path transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"none\" stroke=\"".$polygon_stroke."\" stroke-width=\"".(($ppm*$gap_around_rooms/$x_scale))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  stroke-dasharray='1,1' d=\"M".($x-(8/$x_scale)).",".($y-(($polygon_stroke_w/4)+(16/$y_scale)))."c".(6.628/$x_scale).",0,".(16/$x_scale).",".(5.373/$x_scale).",".(16/$x_scale).",".(16/$x_scale)."\"/>";
					   $line_op_doors .= "<path id = \"D".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\"  stroke-dasharray =\"".($ppm*0.1/$x_scale).",".($ppm*0.1/$x_scale)."\" d = \"M ".(($x+$scaled_door_size/2)-($scaled_gap_around_rooms/4))." ".($y-($scaled_gap_around_rooms))." a ".$scaled_door_size." ".$scaled_door_size." 0 0 0 -".($scaled_door_size-($scaled_gap_around_rooms/2))." -".($scaled_door_size-($scaled_gap_around_rooms))."\"  fill =\"none\" stroke=\"".$polygon_stroke."\" stroke-width=\"".($scaled_gap_around_rooms/2)."\" />";
					  
					  
					  }
					
					
					
					break;	
				case "VD":
					//transform in the form: 822.9471 735.0195,270 (rotation x y);
					$transform = $s['transform'];
					$t_comp = explode(" ", $transform);
					$coord_part = explode(",", $t_comp[1]);
					$x = $coord_part[0];
					$y = $coord_part[1];

					// transform ="rotate(353.74838577062 789.31356,457.89034)" (rot x,y)
					//v;149;815.4884;549.90064;D;6;268.11470657121;;-0.9995 0.0329 -0.0329 -0.9995 815.4884 549.90064 
					//The door refers to lid +1
					//$line_op = "<polygon id = \"D".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".$s['stroke']."\" points=\"".$x.",".($y-$door_size)." ".($x+$door_size).",".($y+$door_size)." ".($x-$door_size).",".($y+$door_size)."\"/>\n";
					//Triangle
					//original if ($door_type == 0) $line_op = "<polygon id = \"D".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$door_fill."\" stroke=\"".$door_color."\" stroke-width=\"".($door_stroke/$x_scale)."\" points=\"".$x.",".( ($y-2*($door_size/$y_scale))-(($door_stroke/$y_scale)/2) )." ".($x+$door_size/$x_scale).",".($y-(($door_stroke/$y_scale)/2))." ".($x).",".($y-($door_stroke/$y_scale)/2)." ".($x-($door_size/$x_scale)).",".($y-(($door_stroke/$y_scale)/2))."\"/>\n";
					if ($door_type == 0) $line_op_doors .= "<polygon id = \"D".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$door_fill."\" stroke=\"".$door_color."\" stroke-width=\"".($ppm*$door_stroke/$x_scale)."\" points=\"".$x.",".($y-($ppm*$door_size/$y_scale)-(0.5*($ppm*$door_stroke/$x_scale)))." ".($x-(0.5*($ppm*$door_size/$x_scale))).",".($y-(0.5*($ppm*$door_stroke/$x_scale)))." ".($x+(0.5*($ppm*$door_size/$x_scale))).",".($y-(0.5*($ppm*$door_stroke/$x_scale)))."\"/>\n";
					
					//CAD style door - normal
					if ($door_type ==1) {
						$scaled_door_size = $ppm*$door_size/$x_scale;
						$scaled_gap_around_rooms=$ppm*$gap_around_rooms/$x_scale;
							
							
					 // $line_op = "<line transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"none\" stroke=\"".$polygon_stroke."\" stroke-width=\"".(($ppm*$gap_around_rooms/$x_scale))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\" stroke-miterlimit=\"10\" x1=\"".($x-(8/$x_scale))."\" y1=\"".($y-($polygon_stroke_w/4))."\" x2=\"".($x-(8/$x_scale))."\" y2=\"".($y-(16/$x_scale))."\"/>";
					 // $line_op .= "<path transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"none\" stroke=\"".$polygon_stroke."\" stroke-width=\"".(($ppm*$gap_around_rooms/$x_scale))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  stroke-dasharray='0.1,0.8' d=\"M".($x-(8/$x_scale)).",".($y-(($polygon_stroke_w/4)+(16/$y_scale)))."c".(6.628/$x_scale).",0,".(16/$x_scale).",".(5.373/$x_scale).",".(16/$x_scale).",".(16/$x_scale)."\"/>";
			
					   $line_op_doors .= "<line transform=\"rotate(".$s['transform'].")\" class = \"door\" fill=\"".$polygon_fill."\" stroke=\"".$polygon_fill."\" stroke-width=\"".($scaled_gap_around_rooms+($scaled_gap_around_rooms*0.4))."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  x1=\"".($x-($scaled_door_size/2)+($scaled_gap_around_rooms))."\" y1=\"".($y-($scaled_gap_around_rooms*0.2))."\" x2=\"".($x+($scaled_door_size/2)-($scaled_gap_around_rooms))."\" y2=\"".($y-($scaled_gap_around_rooms*0.2))."\"/>";
					
					  
					  }
					
					
					
					break;

					
			}
		};
		
		
	}
	

//Now print out entrances. Entrances are not associated with locations so have to be done separately
		$svg = getSVGInfo("bf='$bf'", "");
			
			foreach ($svg as $s) {
				if (($s['type'] == "E") || ($s['type'] == "BE")) { //include bridge entrances - different symbol
						//transform in the form: 270 (rotation x y) 822.9471,735.0195
						$transform = $s['transform'];
						$t_comp = explode(" ", $transform);
						//need to flip triangle by 180deg for entrances so they point into the building
						$coord_part = explode(",", $t_comp[1]);
						$x = $coord_part[0];
						$y = $coord_part[1];
						$angle = floatval($t_comp[0])+180;
						//create new transform for entrances
						$new_transform = $angle." ".$x.",".$y;
						//$line_op = "<polygon id = \"E".$lid."_".$s['index']."\" transform=\"rotate(".$new_transform.")\" class = \"entrance\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".$s['stroke']."\" points=\"".$x.",".($y-$entrance_size)." ".($x+$entrance_size).",".($y+$entrance_size)." ".($x-$entrance_size).",".($y+$entrance_size)."\"/>\n";
				///		$line_op = "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$new_transform.")\" class = \"entrance\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".$s['stroke']."\" points=\"".$x.",".($y-2*$entrance_size-$entrance_stroke)." ".($x+$entrance_size).",".($y+$entrance_stroke)." ".($x).",".($y+$entrance_stroke)." ".($x-$entrance_size).",".($y+$entrance_stroke)."\"/>\n";
						//$line_op = "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$new_transform.")\" class = \"entrance\" display=\"inline\" fill=\"".$s['fill']."\" stroke=\"".$s['color']."\" stroke-width=\"".$s['stroke']."\" points=\"".$x.",".($y-2*$entrance_size-$entrance_stroke/2)." ".($x+$entrance_size).",".($y-$entrance_stroke)." ".($x).",".($y-$entrance_stroke)." ".($x-$entrance_size).",".($y-$entrance_stroke)."\"/>\n";
						
						//Triangles for entrance symbols
						if ($entrance_type ==0) {
						$line_op_entrances .= "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$entrance_fill."\" stroke=\"".$entrance_color."\" stroke-width=\"".($ppm*$entrance_stroke/$x_scale)."\" points=\"".$x.",".($y+($ppm*$entrance_size/$y_scale)-(0.5*($ppm*$entrance_stroke/$y_scale)))." ".($x-(0.5*($ppm*$entrance_size/$x_scale))).",".($y+(0.5*($ppm*$entrance_stroke/$y_scale)))." ".($x+(0.5*($ppm*$entrance_size/$x_scale))).",".($y+(0.5*($ppm*$entrance_stroke/$y_scale)))."\"/>\n";
						$line_op_entrances .= "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$entrance_fill."\" stroke=\"".$entrance_fill."\" stroke-width=\"".($ppm*$entrance_stroke/$x_scale)."\" points=\"".$x.",".($y+($ppm*$entrance_size/$y_scale)-(2*($ppm*$entrance_stroke/$y_scale)))." ".($x-(0.5*($ppm*$entrance_size/$x_scale))).",".($y-(1*($ppm*$entrance_stroke/$y_scale)))." ".($x+(0.5*($ppm*$entrance_size/$x_scale))).",".($y-(1*($ppm*$entrance_stroke/$y_scale)))."\"/>\n";
						
						
						//$line_op .= "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"door\" display=\"inline\" fill=\"".$entrance_fill."\" stroke=\"".$entrance_fill."\" stroke-width=\"".($ppm*$entrance_stroke/$x_scale)."\" points=\"".$x.",".($y+($ppm*$entrance_size/$y_scale)-(2*($ppm*$entrance_stroke/$y_scale)))." ".($x-(0.5*($ppm*$entrance_size/$x_scale))+($ppm*$entrance_stroke/$x_scale)).",".($y-(0.5*($ppm*$entrance_stroke/$y_scale)))." ".($x+(0.5*($ppm*$entrance_size/$x_scale))-($ppm*$entrance_stroke/$x_scale)).",".($y+(0.5*($ppm*$entrance_stroke/$y_scale)))."\"/>\n";
					
						
						
						// $line_op = "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$new_transform.")\" class = \"entrance\" display=\"inline\" fill=\"".$entrance_fill."\" stroke=\"".$entrance_color."\" stroke-width=\"".($ppm*$entrance_stroke/$x_scale)."\" points=\"".$x.",".($y-($ppm*$entrance_stroke/$y_scale))." ".($x+($ppm*$entrance_size/$x_scale)).",".($y-($ppm*$entrance_stroke/$y_scale))." ".($x).",".($y-2*($ppm*$entrance_size/$y_scale))." ".($x-($ppm*$entrance_size/$x_scale)).",".($y-($ppm*$entrance_stroke/$y_scale))."\"/>\n";
						 //$line_op .= "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$new_transform.")\" class = \"entrance\" display=\"inline\" fill=\"".$entrance_fill."\" stroke=\"".$entrance_fill."\" stroke-width=\"".($ppm*$entrance_stroke/$x_scale)."\" points=\"".$x.",".($y-($ppm*$entrance_stroke/$y_scale)+0.2)." ".($x+($ppm*$entrance_size/$x_scale)-($ppm*$entrance_stroke/$x_scale)).",".($y+0.2)." ".($x).",".($y-2*($ppm*$entrance_size/$y_scale)+(2*($ppm*$entrance_stroke/$y_scale)))." ".($x-($ppm*$entrance_size/$x_scale)+($ppm*$entrance_stroke/$x_scale)).",".($y+0.2)."\"/>\n";
						//$line_op .= "<polygon id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$new_transform.")\" class = \"entrance\" display=\"inline\" fill=\"".$entrance_fill."\" stroke=\"".$entrance_fill."\" stroke-width=\"".($ppm*$entrance_stroke/$x_scale)."\" points=\"".$x.",".($y-($ppm*$entrance_stroke/$y_scale))." ".($x+($ppm*$entrance_size/$x_scale)).",".($y-($ppm*$entrance_stroke/$y_scale))." ".($x).",".($y-2*($ppm*$entrance_size/$y_scale))." ".($x-($ppm*$entrance_size/$x_scale)).",".($y-($ppm*$entrance_stroke/$y_scale))."\"/>\n";
						 
						}
						//CAD -type entrance symbols
						if ($entrance_type ==1) {
						
							$scaled_entrance_size = $ppm*$entrance_size/$x_scale;  
							$scaled_perimeter_width=$ppm*$perimeter_width/$x_scale;
							//entrance symbol - double doors						
							if ($s['type'] == "E") {
							  $line_op_entrances .= "<line transform=\"rotate(".$s['transform'].")\" x1=\"".($x-$scaled_entrance_size)."\" y1=\"".($y)."\" x2=\"".($x+$scaled_entrance_size)."\" y2=\"".$y."\" fill =\"none\" stroke=\"".$corridor_colour."\" stroke-width=\"".$scaled_perimeter_width."\" />";
							  $line_op_entrances .= "<path id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"entrance\" display=\"inline\"  stroke-dasharray =\"".($ppm*0.1/$x_scale).",".($ppm*0.1/$x_scale)."\" d = \"M ".$x." ".$y." a ".$scaled_entrance_size." ".$scaled_entrance_size." 0 0 0 -".$scaled_entrance_size." -".$scaled_entrance_size."\"  fill =\"none\" stroke=\"".$outline_stroke."\" stroke-width=\"".($scaled_perimeter_width/2)."\" />";
							  $line_op_entrances .= "<path id = \"E".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"entrance\" display=\"inline\"  stroke-dasharray =\"".($ppm*0.1/$x_scale).",".($ppm*0.1/$x_scale)."\" d = \"M ".$x." ".$y." a ".$scaled_entrance_size." ".$scaled_entrance_size." 0 0 1 +".$scaled_entrance_size." -".$scaled_entrance_size."\"  fill =\"none\" stroke=\"".$outline_stroke."\" stroke-width=\"".($scaled_perimeter_width/2)."\" />";
							  $line_op_entrances .= "<line transform=\"rotate(".$s['transform'].")\" x1=\"".($x-$scaled_entrance_size)."\" y1=\"".($y-$scaled_entrance_size)."\" x2=\"".($x-$scaled_entrance_size)."\" y2=\"".$y."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  stroke-miterlimit=\"10\" fill =\"none\" stroke=\"".$perimeter_colour."\" stroke-width=\"".$scaled_perimeter_width."\" />";	
							  $line_op_entrances .= "<line transform=\"rotate(".$s['transform'].")\" x1=\"".($x+$scaled_entrance_size)."\" y1=\"".($y-$scaled_entrance_size)."\" x2=\"".($x+$scaled_entrance_size)."\" y2=\"".$y."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  stroke-miterlimit=\"10\" fill =\"none\" stroke=\"".$perimeter_colour."\" stroke-width=\"".$scaled_perimeter_width."\" />";	
							}
							//bridge entrance symbol - double doors + bridge rectangle
							if ($s['type'] == "BE") {
							
							//$s['fontsize'] 
							  $line_op_entrances .= "<polygon id =\"Bridge\" class = \"entrance\" display=\"inline\" transform=\"rotate(".$s['transform'].")\" points=\"".($x-$scaled_entrance_size).",".($y)." ".($x+$scaled_entrance_size).",".$y." ".($x+$scaled_entrance_size).",".($y-2*$scaled_entrance_size)." ".($x-$scaled_entrance_size).",".($y-2*$scaled_entrance_size)."\" fill =\"".$corridor_colour."\" stroke=\"".$perimeter_colour."\" stroke-width=\"".$scaled_perimeter_width."\" />";
							 // $line_op_entrances .= "<text transform=\"rotate(".$s['transform'].")\" x = \"".($x-(0.9*$scaled_entrance_size))."\" y = \"".($y-1.4*$scaled_entrance_size)."\" font-family=\"'Arial'\" fill = \"".$location_text_colour."\" font-size=\"3\">Bridge</text>\n";
							  $line_op_entrances .= "<line transform=\"rotate(".$s['transform'].")\" x1=\"".($x-$scaled_entrance_size)."\" y1=\"".($y)."\" x2=\"".($x+$scaled_entrance_size)."\" y2=\"".$y."\" fill =\"none\" stroke=\"".$corridor_colour."\" stroke-width=\"".$scaled_perimeter_width."\" />";
							  $line_op_entrances .= "<path id = \"BE".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"entrance\" display=\"inline\"  stroke-dasharray =\"".($ppm*0.1/$x_scale).",".($ppm*0.1/$x_scale)."\" d = \"M ".$x." ".$y." a ".$scaled_entrance_size." ".$scaled_entrance_size." 0 0 0 -".$scaled_entrance_size." -".$scaled_entrance_size."\"  fill =\"none\" stroke=\"".$outline_stroke."\" stroke-width=\"".($scaled_perimeter_width/2)."\" />";
							  $line_op_entrances .= "<path id = \"BE".($lid)."_".$s['index']."\" transform=\"rotate(".$s['transform'].")\" class = \"entrance\" display=\"inline\"  stroke-dasharray =\"".($ppm*0.1/$x_scale).",".($ppm*0.1/$x_scale)."\" d = \"M ".$x." ".$y." a ".$scaled_entrance_size." ".$scaled_entrance_size." 0 0 1 +".$scaled_entrance_size." -".$scaled_entrance_size."\"  fill =\"none\" stroke=\"".$outline_stroke."\" stroke-width=\"".($scaled_perimeter_width/2)."\" />";
							  $line_op_entrances .= "<line transform=\"rotate(".$s['transform'].")\" x1=\"".($x-$scaled_entrance_size)."\" y1=\"".($y-$scaled_entrance_size)."\" x2=\"".($x-$scaled_entrance_size)."\" y2=\"".$y."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  stroke-miterlimit=\"10\" fill =\"none\" stroke=\"".$perimeter_colour."\" stroke-width=\"".($scaled_perimeter_width)."\" />";	
						      $line_op_entrances .= "<line transform=\"rotate(".$s['transform'].")\" x1=\"".($x+$scaled_entrance_size)."\" y1=\"".($y-$scaled_entrance_size)."\" x2=\"".($x+$scaled_entrance_size)."\" y2=\"".$y."\" stroke-linecap=\"square\" stroke-linejoin=\"square\"  stroke-miterlimit=\"10\" fill =\"none\" stroke=\"".$perimeter_colour."\" stroke-width=\"".($scaled_perimeter_width)."\" />";	
							
							}
							
					}
			
				}
			}
			
//Draw objects in the correct sequence

	fputs ($svg_file_handle, $line_op_perimeters);
	fputs ($svg_file_handle, $line_op_corridors);
	fputs ($svg_file_handle, $line_op_locations);
	fputs ($svg_file_handle, $line_op_doors);	
	fputs ($svg_file_handle, $line_op_entrances);	
	fputs ($svg_file_handle, $line_op_text);	
	
	//reopen w2g file		
	$w2g_file_handle = fopen($w2g_file, "r");
	//Look for perimeter "general" layer - this is drawn first 
	$general_layers = 0; //flag to indicate general layers within W2G file
	while (!feof($w2g_file_handle)) {
        $line = fgets($w2g_file_handle);
        $parts = explode(';', $line);
		if (trim($line) == '') continue;
		//if top general layers in the following format (used for adding general layers after import) - block_general=1 
		//G2;
		//svg stuff
		//G2;
		if ($block_general ==1) {
			if ($general_layers == 1) {
			//echo "writing".$line;
			fputs ($svg_file_handle, $line); //write raw SVG from W2G file to new SVG
			};
			if ($parts[0] == "G2") {
				if ($general_layers == 0) $general_layers = 1;  //opening G2 tag
				else $general_layers = 0; //closing G2 tag
				echo "Match: gl= ".$general_layers;
			};	
		}
		else 
		//top general layer is in this format:
		//G2;svg stuff
		//G2;svg stuff
		{
			if ($parts[0] == "G2") fputs ($svg_file_handle, $parts[1]); //write raw SVG from W2G file to new SVG after "G1" tag		
		}	
	};		
			
	fclose($w2g_file_handle);
	prtSubSec( "Top general layer read created\n");
		
//admin purposes only - for edges and verticies - normally not needed fro final SVG
			
if ($admin ==1) {
	//Now draw edges
	$all_e = getEdgesFloor($bf,"");
	foreach ($all_e as $e){
		$ep = getList_edge_points("eid=".$e->id);
		$px;$py;
		$sv=new vertex($e->get_start());
		$ev=new vertex($e->get_end());
		$sx=($sv->get_lat()-$x_offset)/$x_scale;
		$sy=($sv->get_lon()-$y_offset)/$y_scale; 
		$ex=($ev->get_lat()-$x_offset)/$x_scale;
		$ey=($ev->get_lon()-$y_offset)/$y_scale;
		$line_op = "<line id = \"P".$e->id."\" class = \"path\" fill=\"none\" stroke=\"#FFFFFF\" stroke-linecap=\"round\" stroke-width=\"0.7\" stroke-miterlimit=\"12\" x1=\"".$sx."\" y1=\"".$sy."\" x2=\"".$ex."\" y2=\"".$ey."\"/>\n";
		fputs ($svg_file_handle, $line_op);		
    }
	print "\n".count($all_e)." edges drawn\n";
	
	//Now draw verticies
		$vertex = new vertex();
		$vertex = getList_vertex("bf=$bf");
	   
		for ($i=0; $i<count($vertex); $i++){
			$x=($vertex[$i]->lat-$x_offset)/$x_scale;
			$y=($vertex[$i]->lon-$y_offset)/$y_scale;
		   
			// Red vertices for 3d links
			if ($vertex[$i]->link==""){
			  $line_op = "<circle id = \"V".$vertex[$i]->id."\" class = \"vertex\" fill=\"#ECF70D\" stroke=\"#000000\" stroke-width=\"0.1\" stroke-linecap=\"round\" cx=\"".$x."\" cy=\"".$y."\" r=\"1\"/>\n";
			  fputs ($svg_file_handle, $line_op);
			}
			else {
			  $line_op = "<circle id = \"V".$vertex[$i]->id."\" class = \"vertex\" fill=\"#C9051C\" stroke=\"#000000\" stroke-width=\"0.1\" stroke-linecap=\"round\" cx=\"".$x."\" cy=\"".$y."\" r=\"1\"/>\n";
			  fputs ($svg_file_handle, $line_op);
			}  
		}
		
		print "\n".$i." verticies drawn\n";
		
		
		//write overlay now that all elements have been written to svg
		fputs ($svg_file_handle, $line_op_overlay);
}

//close SVGPan pan/scale "viewport"
$line_op = "</g>";
fputs ($svg_file_handle, $line_op);

//close svg doc
$line_op = "</svg>";
fputs ($svg_file_handle, $line_op);
fclose($svg_file_handle);
prtSubSec( "SVG created");	
	

?>