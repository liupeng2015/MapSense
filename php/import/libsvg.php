<?php
include_once('simple_html_dom.php');
include_once('importconsts.php');
include_once('Tools.php');
include_once('MathTools.php');
#
# Function based lib for svg!
#

// GLOBAL VARIABLES
$LOC_ARR_SIZE=13; // number of fields per location (;;;) without "l;"

$VERT_ARR_SIZE=9; // number of fields per vertex (;;;) without "v;"

// characters to replace into points=
$POINTS_REPLACE=array("\t","\n","\r");


function getVBPngDim($svg, $png_w, &$ppm){
  
  $vb = $svg->viewbox;
  $p=explode(" ",$svg->viewbox);
  //include cases of where there is an offset. Datapoints must be normalised base on 0 0 viewbox
  $svg_w_offset=$p[0];
  $svg_h_offset=$p[1];
  $svg_w=$p[2];
  $svg_h=$p[3];
  
  // Default whatever the user requested
  $png_h = $png_w;
  
  if ($ppm["found"]) {
    prtSubSec("Detecting PNG dimensions based on PPM (".$ppm["ppm"].")");
    
    $svg_w_meters = $svg_w/$ppm["dist"];
    $svg_h_meters = $svg_h/$ppm["dist"];
    prtSubSec("SVG Meters (WxH): ".$svg_w_meters."x".$svg_h_meters);
    
    $png_w = $svg_w_meters*$ppm["ppm"];
    $png_h = $svg_h_meters*$ppm["ppm"];//KG changed from _w_
    prtSubSec("PNG Pixels (WxH): ".$png_w."x".$png_h);
  }
  
  // TODO: Check for non-integer value on image!
  // May be creating the offset issue... (border on tiles)
  $ret=array();
  $ret["swo"]=$svg_w_offset;
  $ret["sho"]=$svg_h_offset;
  $ret["sw"]=$svg_w;
  $ret["sh"]=$svg_h;
  $ret["w"]=$png_w;
  $ret["h"]=$png_h; // intval() here... 
  return $ret;
}


function getPPMInfo($svg,$building){
  global $PPM_IN, $PPM_OUT;
  
 $ret=array();
 $ret["found"] = FALSE;
 
 // Store the used ppm value
 $ret["ppm"] = $PPM_IN;
 if ($building=="0") $ret["ppm"] = $PPM_OUT;
 
 
 
 foreach ($svg->find("g[id=scale-w2g]") as $gl){
  // Search for texts
  foreach ($gl->find("line") as $l){
    
    $ret["found"] = TRUE;
    
    // Distance
    $p1 = new Point($l->x1,$l->y1);
    $p2 = new Point($l->x2,$l->y2);
    $ret["dist"] = Point::dist($p1,$p2);
     
    // W2G String
    $ret["w2gstr"] ="REF1;".$l->x1.";".$l->y1.";\n";
    $ret["w2gstr"].="REF2;".$l->x2.";".$l->y2.";\n";
    // Save used PPM:<SVG UNITS>;<PIXEL> <- both per meter
    $ret["w2gstr"].="PPM;".$ret["dist"].";".$ret["ppm"].";\n";
    
    // Use only the first
    return $ret;
  }
 }
 
 return $ret;
}


//This is a direct copy of the w2g_locations function - better way of consolidating?

function w2g_perimeter($w2gfh, $svg){
  global $last_db_loc, $outline_fill,$outline_stroke,$outline_stroke_w;
  
  $last_id=$last_db_loc+1;
  
  foreach ($svg->find("g[id=perimeter-w2g]") as $gl){
  
      // polygon case
     foreach ($gl->find("polygon") as $l){
      $fill=$outline_fill;
      $stroke=$outline_stroke;
      $stroke_w=$outline_stroke_w;
	  $points = fixPoints($l->points);
      fputs($w2gfh, "P;$last_id;$points;$fill;$stroke;$stroke_w;\n");
      $last_id++;
     }
     
     foreach ($gl->find("polyline") as $l){
      $fill=$outline_fill;
      $stroke=$outline_stroke;
      $stroke_w=$outline_stroke_w;
      $points = fixPoints($l->points);
      fputs($w2gfh, "P;$last_id;$points;$fill;$stroke;$stroke_w;\n");
      $last_id++;
     }
     
     // rect?!
     foreach ($gl->find("rect") as $l){
      $fill=$outline_fill;
      $stroke=$outline_stroke;
      $stroke_w=$outline_stroke_w;
      
      
      $x=$l->x;
      $y=$l->y;
      $w=$l->width;
      $h=$l->height;
	  
      // .... transform....
//       $mat =preg_split("/[() ]/",$l->transform);
      $mat = getTransMatrix($l->transform);
      $points="";
      // No transform
      if (count($mat)<5){
	$x1=$x;
	$y1=$y;
	
	$x2=$x+$w;
	$y2=$y;
	
	$x3=$x+$w;
	$y3=$y+$h;
	
	$x4=$x;
	$y4=$y+$h;
	//KG - all were x1,y1 - changed
	
      // transform!
      }else{
	$a=$mat[1]; 
	$b=$mat[2]; 
	$c=$mat[3]; 
	$d=$mat[4]; 
	$e=$mat[5]; // x
	$f=$mat[6]; // y
	
	
	# Convert to polyline format
	$x1= $a*$x+$c*$y+$e;
	$x2= $a*($x+$w) +$c*($y)+$e;
	$x3= $a*($x+$w) +$c*($y+ $h)+$e;
	$x4= $a*($x) +$c*($y+ $h)+$e;

	$y1= $b*$x+$d*$y+$f;
	$y2= $b*($x+$w) +$d*($y)+$f;
	$y3= $b*($x+$w) +$d*($y+ $h)+$f;
	$y4= $b*($x) +$d*($y+ $h)+$f;  
	
      }
      
      $points="$x1,$y1 $x2,$y2 $x3,$y3 $x4,$y4"; //KG: moved down so that written if no transform - was inside else statement	
      fputs($w2gfh, "P;$last_id;$points;$fill;$stroke;$stroke_w;\n");
      $last_id++;	
     }
  }
$last_db_loc = $last_id-1;    //need this to cancel last $last_id++ on last entry - affects location function
}

function w2g_generalback($w2gfh, $svg){

  // This is drawn first so put any stuff inthis layer that has to de drawn underneath locations
  foreach ($svg->find("g[id=generalback-w2g]") as $pm){
      foreach ($pm->children() as $p){
	$line=str_replace("\r","\n",$p->outertext);
	$line=str_replace("\n","\nG1;",$line);
	fputs($w2gfh, "G1;$line\n");
    }
  }
}

function w2g_general($w2gfh, $svg){

  // For all general layers
  foreach ($svg->find("g[id=general-w2g]") as $gn){
      foreach ($gn->children() as $g){
	$line=str_replace("\r","\n",$g->outertext);
	$line=str_replace("\n","\nG2;",$line);
	fputs($w2gfh, "G2;$line\n");
      }
  }
}


function w2g_room_text($w2gfh, $svg){
	global $use_raw_svg, $font_color, $font_family;
	
  $TEXT_ARR_SIZE = 11;
  
  $last_id=1;
  foreach ($svg->find("g[id=ltext-w2g]") as $gl){
  
    // Search within <g to find texts!
    foreach ($gl->find("text") as $txt){
    
      // Each element (location or so) is represented by an array
      $t = array_fill(0, $TEXT_ARR_SIZE, "");
      $tspans = array();
      
      // Set id
      $t[0]=$last_id;  
      
      // Get name
      $t[7]=trim($txt->innertext); //name
      
      // Ignore empty (library bug) cause by text it also select simple/raw text
      // not only tag text! (<text...>) RAW text does not have SVG syntax, it just looks
      // like 'apsou' so check that we are in an SVG element
      if (strpos ( $txt->outertext, ">" ) == FALSE) {
	continue;
      }
      
      // Transform matrix
//       $mat = preg_split("/[() ]/",$txt->transform);
      $mat = getTransMatrix($txt->transform);

      
      // Font Size (default)
      $t[8]=$txt->getAttribute("font-size");
//	prtSubSec($t[8]);
      
	  if ($use_raw_svg) {
	    $t[9]=$txt->getAttribute("font-family");
	    $t[10]=$txt->getAttribute("fill");
	  }
	  else {
	    //set in project settings (/import/settings)
	    $t[9]= $font_family;
	    $t[10]=$font_color;
	  }
	  
      // Get the name from tspans!
      if ($txt->first_child()!=null){
	$t[7]="";
	foreach ($txt->find("tspan") as $ts){
	  // Create a new text foreach tspan...
	  $t_tmp = array_fill(0, $TEXT_ARR_SIZE, "");
	  
	  // Id has already been used... increment
	  $last_id++;
	  
	  $t_tmp[0] = $last_id;
	  $t_tmp[1]=$mat[1]; 
	  $t_tmp[2]=$mat[2]; 
	  $t_tmp[3]=$mat[3]; 
	  $t_tmp[4]=$mat[4]; 
	  $t_tmp[5]=$mat[5]+$ts->x; // x
	  $t_tmp[6]=$mat[6]+$ts->y; // y
	  $t_tmp[7]=trim($ts->innertext);
	  // replace special unwanted characters
	  $t_tmp[7]=str_replace("&#38;","&",$t_tmp[7]);
	  $t_tmp[7]=str_replace("&#39;","'",$t_tmp[7]);
	  $t_tmp[7]=str_replace("&amp;","&",$t_tmp[7]);
	  $t_tmp[7]=str_replace("&apos;","'",$t_tmp[7]);

	  $t_tmp[8]=$ts->getAttribute("font-size");
	  $t_tmp[9]=$ts->getAttribute("font-family");
	  $t_tmp[10]=$ts->getAttribute("fill");
	  
	  // Add to the $tspan array
	  array_push($tspans, $t_tmp);
	}
      }
      
      // Position
      $t[1]=$mat[1]; 
      $t[2]=$mat[2]; 
      $t[3]=$mat[3]; 
      $t[4]=$mat[4]; 
      $t[5]=$mat[5]; // x
      $t[6]=$mat[6]; // y
      
      // replace special unwanted characters
      $t[7]=str_replace("&#38;","&",$t[7]);
      $t[7]=str_replace("&#39;","'",$t[7]);
      $t[7]=str_replace("&amp;","&",$t[7]);
      $t[7]=str_replace("&apos;","'",$t[7]);
      
      // trim
      $t[7]=trim($t[7]);
    
      // Outer <text> may be empty!!! if it contains only <tspans>
      // so do not save
      if ($t[7]!="") {
	// Save original text
	fputs($w2gfh, "t;");
	fputs($w2gfh, implode(";", $t));
	fputs($w2gfh, ";\n");
      }
      
      // Save all internal tspans
      foreach ($tspans as $t_tmp){
	if ($t_tmp[7]=="") continue;
	fputs($w2gfh, "t;");
	fputs($w2gfh, implode(";", $t_tmp));
	fputs($w2gfh, ";\n");
      }
      $last_id++;
    }
  }
}

function w2g_guiderails($w2gfh, $svg){
  $last_id=1;
  $vgs = array();
  $rails=array();
  foreach ($svg->find("g[id=guiderails-w2g]") as $gl){
      
      // Search within <g to find texts!
      foreach ($gl->find("line") as $l){
	
	$p1 = new Point($l->x1,$l->y1);
	$p2 = new Point($l->x2,$l->y2);
	$skip1 = $skip2 = false;
	
	
	// TODO: Calculation?
	for ($i=0; $i<count($vgs); $i++){
	  if ($vgs[$i]->equals($p1)) {
	    $rstart = $i+1;
	    $skip1 = true;
	  }
	  if ($vgs[$i]->equals($p2)) {
	    $rend = $i+1;
	    $skip2 = true;
	  }
	}
	
	
	if (!$skip1) {
	  fputs($w2gfh, "vg;$last_id;$p1->x;$p1->y;\n");
	  array_push($vgs, $p1);
	  $rstart = $last_id;
	  $last_id++;
	}
	
	// FIXME: WTF rails with the same start/stop!
	if (!$skip2 && !$p2->equals($p1) ) {
	  fputs($w2gfh, "vg;$last_id;$p2->x;$p2->y;\n");
	  array_push($vgs, $p2);
	  $rend = $last_id;
	  $last_id++;
	}
	
	// Add a rail
	array_push($rails, new Point($rstart, $rend));
      }
  }
  
  // Save rails
  $last_id=1;
  foreach ($rails as $r){
    fputs($w2gfh, "GR;$last_id;$r->x;$r->y;\n");
    $last_id++;
  }

}

/**
 * Get a transform and normalize it to a
 * matrix(...,...,...,...)
 */
function getTransMatrix($tr){
  // Replace all commas with spaces
  $tr = str_replace(",", " ", $tr);
  // Now replace multiple spaces to one
  // in case that there was bot , and space...
  $tr = preg_replace("/\s+/", " ", $tr);
  
  $mat = preg_split("/[() ]/",$tr);
  $op = $mat[0];
  if ($op=="matrix"){
    // Nothing...
  }else if ($op=="translate"){
    // Store tx,ty
    $tx = $mat[1];
    $ty = 0;
    if (count($mat)>2) 
      $ty = $mat[2];
      
    // Re-Construct
    $mat[0]="matrix";
    $mat[1]=1;
    $mat[2]=0;
    $mat[3]=0;
    $mat[4]=1;
    $mat[5]=$tx;
    $mat[6]=$ty;
  }else if ($op=="scale"){
    // Store tx,ty
    $sx = $mat[1];
    $sy = $sx;
    if (count($mat)>2) 
      $sy = $mat[2];
      
    // Re-Construct
    $mat[0]="matrix";
    $mat[1]=$sx;
    $mat[2]=0;
    $mat[3]=0;
    $mat[4]=$sy;
    $mat[5]=0;
    $mat[6]=0;
  }
  else if ($op==""){
 // prtE("Line: '$tr'");
  //do nothing;
  }else{
    prtE("UnKnown transform = '$op'");
  }
  
  return $mat;
}


function w2g_doors($w2gfh, $svg){
global $vertices, $last_db_layer,$SVG_LAYER_TYPES,$DOOR_ROOM_OFFSET,$DOOR_COL_OFFSET,$ENTRANCE_ROOM_OFFSET;
$door_count = 0;
foreach ($svg->find("g[id=doors-w2g]") as $gl){
      
      // Search within <g to find polylines
      foreach ($gl->find("polygon") as $l){
	  $door_count++;
	  $points = explode(" ", fixPoints(trim($l->points))); //no need to explode - done in cog function	 
	 	
	  //take centre of gravity of marker - allows the "corrupted markers" to be used
	//function polyGravity requires objects....should change this   
	  
	$min_x = 100000;
	$min_y = 100000;
	$max_x = 0;
	$max_y = 0;
	foreach ($points as $p) {
	 
	  $poly_x_y = explode(',', $p); 
	  if ($poly_x_y[0] > $max_x) $max_x = $poly_x_y[0];
      if ($poly_x_y[0] < $min_x) $min_x = $poly_x_y[0];
      if ($poly_x_y[1] > $max_y) $max_y = $poly_x_y[1];
      if ($poly_x_y[1] < $min_y) $min_y = $poly_x_y[1];
	  }
	  
	$door_x =($max_x+$min_x)/2;
	$door_y =($max_y+$min_y)/2;
	echo ("Door:".$door_count.": ".$door_x.",".$door_y."<br/>");	
	$de_vert = new w2gVertex($door_x, $door_y, "D");
	$de_vert->mat="0";//$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$de_vert);
	//now create a corridor point at the same location for snapping later on
	$c_vert = new w2gVertex($door_x, $door_y, "C");
	$c_vert->mat="0";// 0 ".$door_x." ".$door_y; //$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$c_vert);
	}
 }
}	

function w2g_vdoors($w2gfh, $svg){
global $vertices, $last_db_layer,$SVG_LAYER_TYPES,$DOOR_ROOM_OFFSET,$DOOR_COL_OFFSET,$ENTRANCE_ROOM_OFFSET;
$door_count = 0;
foreach ($svg->find("g[id=vdoors-w2g]") as $gl){
      
      // Search within <g to find polylines
      foreach ($gl->find("polygon") as $l){
	  $door_count++;
	  $points = explode(" ", fixPoints(trim($l->points))); //no need to explode - done in cog function	 
	 	
	  //take centre of gravity of marker - allows the "corrupted markers" to be used
	//function polyGravity requires objects....should change this   
	  
	$min_x = 100000;
	$min_y = 100000;
	$max_x = 0;
	$max_y = 0;
	foreach ($points as $p) {
	 
	  $poly_x_y = explode(',', $p); 
	  if ($poly_x_y[0] > $max_x) $max_x = $poly_x_y[0];
      if ($poly_x_y[0] < $min_x) $min_x = $poly_x_y[0];
      if ($poly_x_y[1] > $max_y) $max_y = $poly_x_y[1];
      if ($poly_x_y[1] < $min_y) $min_y = $poly_x_y[1];
	  }
	  
	$door_x =($max_x+$min_x)/2;
	$door_y =($max_y+$min_y)/2;
	echo ("VDoor:".$door_count.": ".$door_x.",".$door_y."<br/>");	
	$de_vert = new w2gVertex($door_x, $door_y, "VD");
	$de_vert->mat="0";//$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$de_vert);
	//now create a corridor point at the same location for snapping later on
	$c_vert = new w2gVertex($door_x, $door_y, "C");
	$c_vert->mat="0";// 0 ".$door_x." ".$door_y; //$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$c_vert);
	}
 }
}	

function w2g_entrances($w2gfh, $svg){
global $vertices, $last_db_layer,$SVG_LAYER_TYPES,$DOOR_ROOM_OFFSET,$DOOR_COL_OFFSET,$ENTRANCE_ROOM_OFFSET;
$door_count = 0;
foreach ($svg->find("g[id=entrances-w2g]") as $gl){
      
      // Search within <g to find polylines
      foreach ($gl->find("polygon") as $l){
	  $door_count++;
	  $points = explode(" ", fixPoints(trim($l->points))); //no need to explode - done in cog function	 
	 	
	  //take centre of gravity of marker - allows the "corrupted markers" to be used
	//function polyGravity requires objects....should change this   
	  
	$min_x = 100000;
	$min_y = 100000;
	$max_x = 0;
	$max_y = 0;
	foreach ($points as $p) {
	 
	  $poly_x_y = explode(',', $p); 
	  if ($poly_x_y[0] > $max_x) $max_x = $poly_x_y[0];
      if ($poly_x_y[0] < $min_x) $min_x = $poly_x_y[0];
      if ($poly_x_y[1] > $max_y) $max_y = $poly_x_y[1];
      if ($poly_x_y[1] < $min_y) $min_y = $poly_x_y[1];
	  }
	  
	$door_x =($max_x+$min_x)/2;
	$door_y =($max_y+$min_y)/2;
	echo ("Entrance:".$door_count.": ".$door_x.",".$door_y."<br/>");	
	$de_vert = new w2gVertex($door_x, $door_y, "e");
	$de_vert->mat="0";//$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$de_vert);
	//now create a corridor point at the same location for snapping later on
	$c_vert = new w2gVertex($door_x, $door_y, "C");
	$c_vert->mat="0 0 ";// 0 ".$door_x." ".$door_y; //$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$c_vert);
	}
 }
}	

//bridge entrances - these are drawn differently 
function w2g_bentrances($w2gfh, $svg){
global $vertices, $last_db_layer,$SVG_LAYER_TYPES,$DOOR_ROOM_OFFSET,$DOOR_COL_OFFSET,$ENTRANCE_ROOM_OFFSET;
$door_count = 0;
foreach ($svg->find("g[id=bentrances-w2g]") as $gl){
      
      // Search within <g to find polylines
      foreach ($gl->find("polygon") as $l){
	  $door_count++;
	  $points = explode(" ", fixPoints(trim($l->points))); //no need to explode - done in cog function	 
	 	
	  //take centre of gravity of marker - allows the "corrupted markers" to be used
	//function polyGravity requires objects....should change this   
	  
	$min_x = 100000;
	$min_y = 100000;
	$max_x = 0;
	$max_y = 0;
	foreach ($points as $p) {
	 
	  $poly_x_y = explode(',', $p); 
	  if ($poly_x_y[0] > $max_x) $max_x = $poly_x_y[0];
      if ($poly_x_y[0] < $min_x) $min_x = $poly_x_y[0];
      if ($poly_x_y[1] > $max_y) $max_y = $poly_x_y[1];
      if ($poly_x_y[1] < $min_y) $min_y = $poly_x_y[1];
	  }
	  
	$door_x =($max_x+$min_x)/2;
	$door_y =($max_y+$min_y)/2;
	echo ("Bridge Entrance:".$door_count.": ".$door_x.",".$door_y."<br/>");	
	$de_vert = new w2gVertex($door_x, $door_y, "be");
	$de_vert->mat="0";//$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$de_vert);
	//now create a corridor point at the same location for snapping later on
	$c_vert = new w2gVertex($door_x, $door_y, "C");
	$c_vert->mat="0 0 ";// 0 ".$door_x." ".$door_y; //$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	array_push($vertices,$c_vert);
	}
 }
}	

function w2g_layers($w2gfh, $svg){
  global $vertices, $last_db_layer,$SVG_LAYER_TYPES,$DOOR_ROOM_OFFSET,$DOOR_COL_OFFSET,$ENTRANCE_ROOM_OFFSET;

  $last_id=$last_db_layer+1;
  $old_door_style = 0; //flag to indicate old or new door style (old type has text and polynomial in layer)
  
  // For each layer
  foreach ($SVG_LAYER_TYPES as $key=>$value){
	
/*example door format...
<g id="ACAD_x5F_PROXY_x5F_ENTITY">
		<text transform="matrix(0 -1 1 0 21.2969 48.2734)" fill="#FF7F00" font-family="'MyriadPro-Regular'" font-size="0.3328">Door2</text>
		
			<polyline fill="none" stroke="#FF7F00" stroke-width="0.01" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" points="
			21.185,48.35 20.996,48.35 20.996,47.777 20.996,47.207 21.185,47.207 21.373,47.207 21.373,47.777 21.373,48.727 21.185,48.35 		
			"/>
		
			<line fill="none" stroke="#FF7F00" stroke-width="0.01" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="10" x1="21.373" y1="48.35" x2="20.996" y2="47.207"/>
	</g>

*/
   
    // Grab g
    $tmp_c = 0;
    foreach ($svg->find("g[id=$key]") as $g){
	
// 	foreach ($g->find("polyline") as $pl){	
// 		//KG testing - use the point of the marker as centre of door	
// 		// Get polyline within the <g> tag			
// 		$points = explode(" ", fixPoints(trim($pl->points)));
// 		//$first = explode(",",$points[0]);
// 		$penultimate = explode(",",$points[count($points)-2]);
// 		prtSubSec("Door centre point = ".$penultimate[0].", ".$penultimate[1]);
// 		
// 		//New x and y as the point of the marker for door and and one of the rectangle points for room markers
// 	}
// 	
      // Get texts!
      foreach ($g->find("text") as $l){
      
	// LIBRARY BUG!
	if (strpos ( $l->outertext, ">" ) == FALSE) continue;
	
	$line=array();
	
	$line[0]="LP";
	$line[1]=$last_id;
	// Get transform matrix
	$mat = getTransMatrix($l->transform);
	// DO THE DEFAULT - CHANGE LATER
	$line[2]=$mat[5]; // x
	$line[3]=$mat[6]; // y

	// Urban
	// ONLY FOR DOORS AND ENTRANCES SINCE THE OTHER LAYER MAY NOT HAVE A POLYLINE 
	if (($key=="doors-w2g") || ($key=="entrances-w2g")||($key=="vdoors-w2g") ){
	  $apsou = $l->nextSibling();
	  echo "Using old style";
	  echo "Door type: ".$key."<br/>";
	  $old_door_style = 1;
	
	  // Should NEVER happen
	  if (!$apsou){
	    echo "Problem - no door polygon!";
	  }
	  $points = explode(" ", fixPoints(trim($apsou->points))); //no need to explode - done in cog function
	
	//take centre of gravity of marker - allows the "corrupted markers" to be used
	//function polyGravity requires objects....should change this   
	  
	$min_x = 100000;
	$min_y = 100000;
	$max_x = 0;
	$max_y = 0;
	foreach ($points as $p) {
	  $poly_x_y = explode(',', $p); 
	  
	  if ($poly_x_y[0] > $max_x) $max_x = $poly_x_y[0];
      if ($poly_x_y[0] < $min_x) $min_x = $poly_x_y[0];
      if ($poly_x_y[1] > $max_y) $max_y = $poly_x_y[1];
      if ($poly_x_y[1] < $min_y) $min_y = $poly_x_y[1];
	  }
	  
	$line[2] =($max_x+$min_x)/2;
	$line[3] =($max_y+$min_y)/2; 
	echo ("Door using text: ".$line[2].",".$line[3]."<br/>");	    
	} 	
	$line[4]=$value["w2g"];
	
	// Check for doors/entr and add vertex!
	if ($value["vertex"]){
	  		
	  //KG now the door fitting routine snaps to walls, don't need to move door and corridor points by offset
	  //Angle may not be known	
		
	  // Create vertex for this entrance/door
	  $room_x = $line[2]; 
	  $room_y = $line[3]; 
	  //If entrance, door offset is different since may be a different size
	  //within the building, not outwards - triangle points inwards to building
	  /*if ($key=="entrances-w2g"){
		$room_x = $line[2]; 
		$room_y = $line[3]; 
	  }
	  */
	  
	  $ass_t=$value["w2g"];
	  $de_vert = new w2gVertex($room_x, $room_y, $ass_t);
	  $de_vert->mat=$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	  array_push($vertices,$de_vert);
	  
	  // Create a vertex for the corridor 
	  $corridor_x = $line[2]; 
	  $corridor_y = $line[3]; 
	  /*
	  //If entrance, corridor vertex to go inwards towards the guiderails
	  //within the building, not outwards.
	  if ($key=="entrances-w2g"){
		$corridor_x = $line[2]; 
		$corridor_y = $line[3]; 
	  }
	  */
	  
	  $c_vert = new w2gVertex($corridor_x, $corridor_y, "C");
	  $c_vert->mat=$mat[1]." ".$mat[2]." ".$mat[3]." ".$mat[4];
	  array_push($vertices,$c_vert);
	}
	
	// If it is a layer but not a vertex, save layer point
	if ($value["layer"] && !$value["vertex"]){
	  // Save Layer Point
	  fputs($w2gfh,implode(";", $line));
	  fputs($w2gfh,"\n");
	  $last_id++;
	}
	
	if ($value["cid"]){
	  $line[0]="CID";
	  fputs($w2gfh,implode(";", $line));
	  fputs($w2gfh,"\n");
	  $last_id++;
	}
      }
	
    } // end of layer
  
  }// and of all

if ($old_door_style == 0) {
	echo ("No old style doors found, trying new door style");
	w2g_doors($w2gfh, $svg); //no old door styles found, try new style
	w2g_vdoors($w2gfh, $svg); //no old door styles found, try new style
	w2g_entrances($w2gfh, $svg); //no old door styles found, try new style
	w2g_bentrances($w2gfh, $svg); //no old door styles found, try new style
} 
}


/**
 * Save the previously added vertices. Also save the vertices
 * found from the "paths". Finally this function uses the global 
 * edges array and adds some edges in there.
 * 
 * 
 * PARSE ALSO THE POLYLINES!
 */
function w2g_paths($w2gfh, $svg){
  global $vertices, $edges, $last_db_vertex, $last_db_edge;
  
  // Count and write old vertices
  $last_id=1;
  foreach ($vertices as $v){
    $v->id = $last_id;
    fputs($w2gfh, $v->w2gString().";\n");
    $last_id++;
  }
  
  foreach ($svg->find("g[id=paths-w2g]") as $gl){
      
      // Search within <g to find LINES!
      foreach ($gl->find("polyline") as $l){
	
	$points = explode(" ", fixPoints(trim($l->points)));
	
	$first = explode(",",$points[0]);
	$last = explode(",",$points[count($points)-1]);
	 
	
	$p1 = new w2gVertex($first[0],$first[1]);
	$p2 = new w2gVertex($last[0],$last[1]);
	
	// Check is a vertex already exists 
	// See w2gVertex::equals for error estimation
	$skip1 = $skip2 = false;
	
	
	// TODO: Calculation?
	for ($i=0; $i<count($vertices); $i++){
	  if ($vertices[$i]->equals($p1)) {
	    $rstart = $i+1;
	    $skip1 = true;
	  }
	  if ($vertices[$i]->equals($p2)) {
	    $rend = $i+1;
	    $skip2 = true;
	  }
	}
	
	// Add the "start" vertex
	if (!$skip1) {
	  $p1->id = $last_id;
	  fputs($w2gfh, $p1->w2gString()."\n");
	  array_push($vertices, $p1);
	  $rstart = $last_id;
	  $last_id++;
	}
	
	if ($p2->equals($p1)) {
	  echo "WARNING: WARNING: Same p1,p2 in paths! $p1 $p2\n";
	  continue;
	}
	if (!$skip2 && !$p2->equals($p1) ) {
	  $p2->id = $last_id;
	  fputs($w2gfh, $p2->w2gString()."\n");
	  array_push($vertices, $p2);
	  $rend = $last_id;
	  $last_id++;
	}
	
	// Add a edge between these 2
	$e=new w2gEdge($rstart, $rend);
	$stroke=$l->stroke;
	
	// Decide inclination and stairs...
	if ($stroke =="#123456") $e->stairs = "Y";
	else if ($stroke =="#101010") $e->inc = 10;
	else if ($stroke =="#505050") $e->inc = 50;
	else if ($stroke =="#100100") $e->inc = 100;
	
	// Add the 1-(len-2) points (if there) as
	// edge_points
	$e->points="";
	for ($i=1; $i<count($points)-2; $i++){
	  $e->points.= $points[$i]." ";
	}
	

	array_push($edges, $e);
      }
  }
  
  // Write Edges
  $last_id=$last_db_edge+1;
  foreach ($edges as $e){
    $e->id = $last_id;
    fputs($w2gfh, $e->w2gString()."\n");
    $last_id++;
  }

}

function fixPoints($points){
  global $POINTS_REPLACE;
  // Remove illigal chars
  $points = str_replace($POINTS_REPLACE, "",$points);
  // Remove multiple spaces
  $points = preg_replace("/[[:blank:]]+/"," ",$points);
  
  return $points;
}

function w2g_locations($w2gfh, $svg){
  global $use_raw_svg,$last_db_loc,$polygon_fill,$polygon_stroke,$polygon_stroke_w;
  
  $last_id=$last_db_loc+1;
  
  foreach ($svg->find("g[id=locations-w2g]") as $gl){
  
      // polygon case
     foreach ($gl->find("polygon") as $l){
      if ($use_raw_svg) {
		  $fill=$l->fill;
		  $stroke=$l->stroke;
		  $stroke_w=$l->getAttribute("stroke-width");
	  }
		  
      //Use project settings (import/settings) for room parameters
      else {
	   $fill=$polygon_fill;
       $stroke=$polygon_stroke;
       $stroke_w=$polygon_stroke_w;
	  }
      $points = fixPoints($l->points);
      fputs($w2gfh, "P;$last_id;$points;$fill;$stroke;$stroke_w;\n");
      $last_id++;
     }
     
     foreach ($gl->find("polyline") as $l){
	 if ($use_raw_svg) {
		 
		  $fill=$l->fill;
		  $stroke=$l->stroke;
		  $stroke_w=$l->getAttribute("stroke-width");
	  }
	  else {
        $fill=$polygon_fill;
        $stroke=$polygon_stroke;
        $stroke_w=$polygon_stroke_w;
	  }
      $points = fixPoints($l->points);
      fputs($w2gfh, "P;$last_id;$points;$fill;$stroke;$stroke_w;\n");
      $last_id++;
     }
     
     // rect?!
     foreach ($gl->find("rect") as $l){
	 if ($use_raw_svg) {
		 
		  $fill=$l->fill;
		  $stroke=$l->stroke;
		  $stroke_w=$l->getAttribute("stroke-width");
	  }
	  else {
      $fill=$polygon_fill;
      $stroke=$polygon_stroke;
      $stroke_w=$polygon_stroke_w;
      }
      
      $x=$l->x;
      $y=$l->y;
      $w=$l->width;
      $h=$l->height;
      
	  
	  
      // .... transform....
//       $mat =preg_split("/[() ]/",$l->transform);
      $mat = getTransMatrix($l->transform);
      $points="";
      // No transform
      if (count($mat)<5){
	$x1=$x;
	$y1=$y;
	
	$x2=$x+$w;
	$y2=$y;
	
	$x3=$x+$w;
	$y3=$y+$h;
	
	$x4=$x;
	$y4=$y+$h;
	//KG - all were x1,y1 - changed
	
      // transform!
      }else{
	$a=$mat[1]; 
	$b=$mat[2]; 
	$c=$mat[3]; 
	$d=$mat[4]; 
	$e=$mat[5]; // x
	$f=$mat[6]; // y
	
	
	# Convert to polyline format
	$x1= $a*$x+$c*$y+$e;
	$x2= $a*($x+$w) +$c*($y)+$e;
	$x3= $a*($x+$w) +$c*($y+ $h)+$e;
	$x4= $a*($x) +$c*($y+ $h)+$e;

	$y1= $b*$x+$d*$y+$f;
	$y2= $b*($x+$w) +$d*($y)+$f;
	$y3= $b*($x+$w) +$d*($y+ $h)+$f;
	$y4= $b*($x) +$d*($y+ $h)+$f;  
	
      }
      
      $points="$x1,$y1 $x2,$y2 $x3,$y3 $x4,$y4"; //KG: moved down so that written if no transform - was inside else statement	
      fputs($w2gfh, "P;$last_id;$points;$fill;$stroke;$stroke_w;\n");
      $last_id++;
      
     }
  }
}


function w2g_aliases($w2gfh, $svg){
  
 foreach ($svg->find("g[id=ltext2-w2g]") as $gl){
  // Search for texts
  foreach ($gl->find("text") as $a){
  
    // LIBRARY BUG!: text returns any characters that are not in tag too
    if (strpos ( $a->outertext, ">" ) == FALSE) continue;
    
    $line=array();
    
    
    // Get transform matrix
//     $mat=preg_split("/[() ]/",$a->transform);
    $mat = getTransMatrix($a->transform);
    
    $w2ga = new w2gAlias($mat[5],$mat[6],trim($a->innertext),"");
    
    fputs($w2gfh,$w2ga->w2gString()."\n");
  }
 }
}



?>
