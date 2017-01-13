<?php
include_once('importconsts.php');
include_once('Tools.php');
include_once('MathTools.php');
include_once('libmath.php');

// Load Configuration
prtSubSec("Processing objects");
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

// Setup files
$fbname=basename($fname,".w2g");
$w2g_file = $fname;
$svg_file = $PROJECT_DIR."/".$building.$floor.$room."/".$fbname."_geo.svg";
$new_w2g_file = $PROJECT_DIR."/".$building.$floor.$room."/".$fbname.$GEO_EXT;

//Open up file and start reading
$w2g_file_handle = fopen($w2g_file, "r");
$svg_file_handle = fopen($svg_file, "w");
$new_w2g_file_handle = fopen($new_w2g_file, "w");

$line = fgets($w2g_file_handle);

$last_edge = 0;

$last_vertex = 1;

// Objects
$verts=array();
$txts=array();
$P_names=array();
$CIDs = array();
$LPs = array();
$aliases = array();
// delayed locations
$SVGLOC = array();
$W2GLOC = array();

while (!feof($w2g_file_handle)) {
        $line = fgets($w2g_file_handle);
        $parts = explode(';', $line);
		
    switch ($parts[0]) {
	// Project
	case "p":
		fputs ($new_w2g_file_handle, $line);

		$w2g_project=$parts[1];
		$w2g_building=$parts[2];
		$w2g_floor=$parts[3];
		print $w2g_project.$w2g_building.$w2g_floor."\n";
		break;
		
	// ViewBox
	case "vb":
		$line_op = $line;
		fputs ($new_w2g_file_handle, $line_op);
		
		$width = $parts[1];
		$height = $parts[2];
		$line_op =  "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
		$line_op .= "<!DOCTYPE svg PUBLIC \"-//W3C//DTD SVG 1.1//EN\" \"http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\">\n";
		$line_op .= "<svg version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" x=\"0px\" y=\"0px\"\n";
		$line_op .= "width=\"100%\" height=\"100%\" viewBox=\"0 0 ".$width." ".$height."\" enable-background=\"new 0 0 ".$width." ".$height."\"\n";
		$line_op .= "xml:space=\"preserve\">\n";
 		$line_op .= "<defs>\n";
// 		$line_op .= "<script  xlink:href=\"http://www.wai2go.co.uk/RL3/lib_svg/js/click_svg_element.js\"/>\n";
 		$line_op .= "<script  xlink:href=\"http://www.wai2go.co.uk/RL3/lib_svg/js/jquery-1.7.1.min.js\"/>\n";
 		$line_op .= "<script  xlink:href=\"http://www.wai2go.co.uk/RL3/lib_svg/js/SVGPan_admin.js\"/>\n";
 		$line_op .= "</defs>\n";
		$line_op .= "<g id = \"viewport\">\n";
// 		$line_op .= "<polygon fill=\"".$background_colour."\" stroke=\"".$background_colour."\" points=\"0,0 0,".$height." ".$width.",".$height." ".$width.",0\"/>\n";
		
		fputs ($svg_file_handle, $line_op);
		
		break;
	// Perimeter layer - first to be drawn
	case "G1":
		fputs ($new_w2g_file_handle, $line);
		fputs ($svg_file_handle, $parts[1]);
		break;
		
	// GuideRails
	case "GR":
		fputs ($new_w2g_file_handle, $line);
		break;
		
	// Guiderail Vertex
	case "vg":
		fputs ($new_w2g_file_handle, $line);
		break;	
	
	// Layer Points LP;51;818.0518;564.0039;S
	case "LP":
		// UrbaN: Layer Points Must go back to be imported from w2g2db!
		// we do use them tho later to identify vertx->link. If an LP
		// exists in the poly, the vertex we add has link!
		
		// Save at the end! Cause they are moved...
		//fputs ($new_w2g_file_handle, $line);
		
		array_push ($LPs, w2gLayerPoint::getFromParts($parts));
		break;
	case "CID":
		// UrbaN: CIDs are like layers but they are only used
		// to define/mark the l->layer. Not used anywhere after 
		// this stage...
		//fputs ($new_w2g_file_handle, $line);
		array_push ($CIDs, w2gCID::getFromParts($parts));
		break;
	case "v":
		// create new vertex: Keep ids cause they are used later!
		$verts[$parts[1]]=w2gVertex::getFromParts($parts);
		//****************************************************************
		$last_vertex = $parts[1];
		break;
		
	//Paths--> e;49;385;386;;;21.9928;G
	case "e":
		//[1] = id, [2] = start Vertex, [3] = end vertex
		// Urban Never happeing !: if ($parts[7] != "G"){
		// Write on svg...
		$v1=$verts[$parts[2]];
		$v2=$verts[$parts[3]];
		$line_op = "<line id = \"P".$parts[1]."\" fill=\"none\" stroke=\"".$path_colour."\" stroke-linecap=\"round\" stroke-width=\"4\" stroke-miterlimit=\"12\" x1=\"".$v1->x."\" y1=\"".$v1->y."\" x2=\"".$v2->x."\" y2=\"".$v2->y."\"/>\n";
		fputs ($svg_file_handle, $line_op);
		//}
		fputs ($new_w2g_file_handle, $line);
		// store last edge which is added upon later
		// edges are dynamically added directly to the file... so we need this
		$last_edge  = $parts[1]; 
		break;
	// Text values : t;1;0.9941;0.1089;-0.1089;0.9941;413.3789;826.5752;Lift;1.5 (font size)
	case "t":
		$txts[$parts[1]] = w2gText::getFromParts($parts);
		break;
	case "A":
		// store for detecting their lid 
		// save at the end
		$aliases[]=w2gAlias::getFromParts($parts);
		break;
	// Polynomials
	// P;points;fill;stroke;stroke-width
	case "P":
		$location_name = "";
		$door_poly = "";
		// Reset layer flag - contains the layer/CID that this location belongs to
		$layer="";
		//records the text indicies so that can be used to create W2G - this is a concatenation of text entries within the location polygon
		$text_indicies = "";
		$P_ID = $parts[1];	
		$P_points = str_replace("\t", "", trim($parts[2]));
		$P_fill = trim($parts[3]);
		$P_colour = trim($parts[4]);
		$P_stroke = trim($parts[5]);	
		$last_vertex++;
		
		// FIXME: Check the following code...
		// change - this is a roundabout way of doing this!!! Don't break up
		$poly_points = explode(' ', $P_points);	//separate into x,y points: format = x,y x1,y1 x2,y2 etc...		
		//separate into individual x and y value
		$poly_x_y = explode(',', $poly_points[0]);
		foreach ($poly_points as $PPtest) {
			$pptest = explode(',', $PPtest);
		}	
		// Find average of bounding box 
		// Can't take average of points since points not equally spread around polygon
		
		$poly_av = polyGravity($poly_points);
		
		//Add a check for duplicated polypoints (cog will be the same) - will eliminate issue of duplciated polynomials
		//TO DO
		
		
		// Create new vertex (assoc with the new location)
		$new_v = new w2gVertex();
		$new_v->id = $last_vertex;
		$new_v->x = $poly_av->x;
		$new_v->y = $poly_av->y;
		
		// UrbaN: Use text location instead of poly.
		// to be used later on text
		$gravityOutOfPoly = false;
		if (!insidePoints($poly_av, $poly_points)) {
		  $gravityOutOfPoly=true;
		}
		
		// Store...
		$verts[$last_vertex]=$new_v;
		$C_flag = 0; //flag to indicate that corridor has already been found - 
		$P_flag = 0; //flag to indicate that perimeter has already been found -
		// Look for CID for this location
		foreach ($CIDs as $cid){
		  //$delta = abs($cid->x - $poly_av->x);
		  // vertex outside of the poly!? (maybe predef large area to speed up!)
		  //if ($delta>=$geo_area) continue;
		  // Check area!
		  $result=insidePoints($cid, $poly_points);
		  if ($result != true) continue;
		  $layer = $cid->type;
		  //Building outlines will give true result for every tag..break out of loop if perimeter layer
		  if ($layer == "BO") {
		    $P_flag =1;
		    continue;
		  }
		  if ($layer == "CO") $C_flag =1;
//		  if ($layer == "CO") {
//		    break;
//		  }
		  //Had a big problem here...can't "continue" these function otherwise the BO becomes a corridor!
		  //Allows for when a corridor has been drawn wrongly and encloses a toilet/stair etc. The corridor polygon will not take
	      //these attributes
		}
		//Now check to see if any of the labels were corridor label and make a corridor location if it was
		if ($P_flag == 1) $layer = "BO";
		if (($C_flag == 1) && ($P_flag == 0)) $layer = "CO";
		
		// For all vertices find doors and connect them to the room
		// vertex we just created
		$door_count=0;
		foreach ($verts as $vertex)
		{
			if ($layer == "BO") continue; //Don't want to create routing paths to building outline
			//KG:Don't want to skip - need to change door verticies that are within corridors to a "cd" type
			//if ($layer == "CO") continue; //Don't want to create routing paths to corridors	
			// Already associated vertex
			if ($vertex->ass_id!=0) continue;
			// Already parsed or no association...
			// Look for anything that has vertex 
			// (Stairs can have vertex but no location)
			if (!isVertexW2GType($vertex->ass_t)) continue;
			
			//$delta = abs($vertex->x - $poly_av->x);
			
			// vertex outside of the poly!? (maybe predef large area to speed up!)
			//if ($delta>=$geo_area) continue;
			
			// Check area!
			$result=insidePoints($vertex, $poly_points);
			// Check result before moving on
			if ($result != true) continue;
			
			// +1 Door for this location
			$door_count++;
			$vertex->ass_id = $P_ID;
			$next_v = $verts[$vertex->id+1];
			
			if ($layer == "CO") {
			  $vertex->ass_t = "CD";	
			  $next_v->ass_t = "CC";  
			}
			
			// angle is with ref to +90 --->East
			// remember that "C" corridor point is +1 after door vertex - 
			// use this as the bearing line. Normalised wrt to door vertex
			
			
			
			
			if ($layer != "CO") {
			$icon_angle = Point::bearing($vertex, $next_v);  
			$vertex->angle = $icon_angle;		
			// Add a new edge connecting the last_vertex(which we just added for this location)
			// with the current one (which is the door vertex)
			//KG: Don't do for corridors
			
			 $new_edge = new w2gEdge($last_vertex,$vertex->id);
			// Set Distance
			 $new_edge->length = Point::dist($vertex, $verts[$last_vertex]);
			}
			if (( $layer != "BO") && ($layer != "CO")) {
				$last_edge++;
				$new_edge->id = $last_edge;
				fputs ($new_w2g_file_handle, $new_edge->w2gString()."\n");
			}
		} // END of VERTICES CHECKs
		
		//CID check was here...moved up
	  
		// Now search all the text verticies imported to see if they are within polyline
		$location_name = "";
		$goopdone = false;
		foreach ($txts as $txt)
		{
		if ($layer == "CO") continue;
		if ($layer == "BO") {
			$location_name = $building."_".$floor."_Perimeter"; //KG
			continue; //Don't do this for building outlines since everyting will be inside
		};
				
		    // ... delta check
		    //$delta = abs($txt->x - $poly_av->x);
		    //if ($delta>=$geo_area) continue;
		    
		    $result=insidePoints($txt, $poly_points);
		    if (!$result) continue;
		    
		    // Urban: if the current polygon gravity is out of it 
		    // and we haven't used text's coords... use them
		    if ($gravityOutOfPoly && !$goopdone){
		      // Update both last vertex and poly_av
		      $verts[$last_vertex]->x = $poly_av->x = $txt->x;
		      $verts[$last_vertex]->y = $poly_av->y = $txt->y;
		      $goopdone = true;
		    }
//zz5		    
//KG
//move text the the centre of gravity of the room 3- digit text is approx 7px wide and 5px high- allow for this
if ($txt2loc_centre){
$txt->x = $poly_av->x -5.5;   //put into variable eventually
$txt->y = $poly_av->y +1;
}
		    if ($location_name != "") {
		      $location_name .= " ".$txt->text;
		      // Record the text index for creating W2G file later
		      $text_indicies .= ",".$txt->id;
		    }
		    else{ 
		      $location_name = $txt->text;
		      $text_indicies = $txt->id; 
		    }
		}
		
		//Scan for duplicate names add suffix afterwards is so
		if ($location_name == "") {
			// Give location a name if no text found in polynomial
			// This is later renumbered when importing into database
			// according to db ID. "Lxx" denotes a non-user name
			$location_name = "Lxx".$P_ID;
		}
		
	      
		// Check for duplicates 
		// into database since may be duplictates from
		// other floors also so pointless doing it here
		//This has to be changed
		/* zz 取消随机数命名
		if ( ($location_name != "") && (substr($location_name,0,3) != "Lxx") ){
		    foreach ($P_names as $tmp_name){
			if (strcmp($tmp_name,$location_name)==0) {
			  $location_name = $location_name."_".rand() ; // this ensures no duplicate names !   $duplicates;
			  prtE( "Duplicated name for location:".$tmp_name.". Renaming to: ".$location_name);
			  $P_names[$P_ID] = $location_name;
			  break;
			}
		    }
		}*/
		
		
		// Store the names of locations to check for duplicates
		$P_names[$P_ID] = $location_name; 
		
		
		// Look for LPs with link for this location/vertex
		$link = "";
		foreach ($LPs as $lp){
			if ($layer == "BO") continue; //Don't do this for building outlines since everyting will be inside
			if ($layer == "CO") continue; //Don't do this for corridor outlines since everyting will be inside
		
		  $tmp_link = getLinkFromW2GType($lp->type);
		  
		  if ($tmp_link=="") continue;
		  //$delta = abs($lp->x - $poly_av->x);

		  // vertex outside of the poly!? (maybe predef large area to speed up!)
		  //if ($delta>=$geo_area) continue;
		  
		  // Check area!
		  $result=insidePoints($lp, $poly_points);
		  
		  if ($result != true) continue;
		  $link = $tmp_link.rand();
		  
		  // Move the layer to match the grav. point
		  $lp->x = $poly_av->x;
		  $lp->y = $poly_av->y;
		  
		  // Here set LAYER to change color later!
		  // if this failes just change the P_Fill directly
		  $layer=$lp->type;
		}
		

		$verts[$last_vertex]->ass_t = "l";
		$verts[$last_vertex]->ass_id = $P_ID;
		$verts[$last_vertex]->link=$link;
		
		// Add new location in the w2g 
		$tmp_l = new w2gLocation($poly_av->x, $poly_av->y, $P_ID, $location_name);
		$tmp_l->points = $P_points;
		
		// include colour and stroke thickness ....
//		$printOrder = false;
		if ($layer!=""){
		  $tmp_fill=getFillFromW2Gtype($layer);
		  if ($tmp_fill!="") $P_fill = $tmp_fill;
//		  $printOrder = getPrintOrderForw2gType($layer);
		}
		
		$tmp_l->fill = $P_fill;
		$tmp_l->layer = $layer;
		$tmp_l->color = $P_colour;
		
		$tmp_l->text_indexes = $text_indicies; 
		$line_op = $tmp_l->w2gString()."\n";
		fputs ($new_w2g_file_handle, $line_op);
		
		
		// Add Location to the SVG
		$line_op = $tmp_l->svgString()."\n";
        if ($door_poly!="") $line_op.=$door_poly;
        fputs ($svg_file_handle, $line_op);
		
//		$line_op2 = $tmp_l->svgString()."\n";
		
/*		if ($door_poly!="") $line_op2.=$door_poly;
		
		if ($printOrder!==false) {
		// add only if it has early print
		  fputs ($svg_file_handle, $line_op2);
		  fputs ($new_w2g_file_handle, $line_op);
		}
		else {
		  array_push($SVGLOC, $line_op2);
		  array_push($W2GLOC, $line_op);
		}
*/
		
		// Put back texts on both SVG and w2g
		if ($text_indicies != "") {
			$matching_text  = explode(",",$text_indicies);
			$t_numb = count($matching_text);
			
			// Find the total height of all texts
			// adding a padding between them
// 			$totalHeight=0;
// 			for ($t=0; $t<$t_numb;$t++) {
// 			  $tmpid = $matching_text[$t];
// 			  $txts[$tmpid]->fontfamily="Arial";
// 			  $tsize = getStringPxSize($txts[$tmpid]->text,$txts[$tmpid]->fontfamily, 
// 						   12, $txts[$tmpid]->fontsize); // 12 will be ignored
// 			  $totalHeight+=$tsize["h"];
// 			}
// 			$nextFont_y = $poly_av->y - $totalHeight/2;
			
			
			// Put Texts Back! Urban: Not ALL texts are going back!
			for ($t=0; $t<$t_numb;$t++) {
			  $tmpid = $matching_text[$t];
			  $txts[$tmpid]->loc = $P_ID;  
			  $txts[$tmpid]->index = $t;
			  
			  
// 			  $tsize = getStringPxSize($txts[$tmpid]->text,$txts[$tmpid]->fontfamily, 
// 						   12, $txts[$tmpid]->fontsize); // 12 will be ignored
// 			  $nextFont_y+=$tsize["h"];
// 			  $txts[$tmpid]->y = $nextFont_y;
// 			  $txts[$tmpid]->x = $poly_av->x-$tsize["w"]/2;
			  
  
			  // Put back to w2g
			  $line_op = $txts[$tmpid]->w2gString()."\n";
			  fputs ($new_w2g_file_handle, $line_op);
			  
			  // Back 2 SVG!
			  $line_op = $txts[$tmpid]->svgString()."\n";
			  fputs ($svg_file_handle, $line_op);
			}
		}
		
		// Search for aliases
		// Check for duplicates
		$dup = array();
		foreach ($aliases as $a){
		    if ($a->lid!="") continue;
		    // ... delta check
		    // do not do delta check!!! Huge room
		    //$delta = abs($a->x - $poly_av->x);
		    //if ($delta>=$geo_area) {
		    //  echo "Skipping link=".$a->lid."$delta \n";
		    //  continue;
		    //}
		    
		    
		    $result=insidePoints($a, $poly_points);
		    if (!$result) continue;
		    
		    // Add prefix - floor
		   // $a->text=$location_name."_".$a->text; //KG removed
		    
		    // Duplicates BEFORE storing
		    foreach ($aliases as $a2){
		      if ($a2->lid!=$P_ID) continue;
		      if ($a->text != $a2->text) continue;
		      
		      // same lid same text!
		      $idx=1;
		      if (array_key_exists($a2->text, $dup)) $idx=$dup[$a2->text];
		      
		      // rename
		      $a->text.="_$idx";
		      
		      // store index
		      $dup[$a->text] = $idx+1;
		    }
		    
		    // Store location ID
		    $a->lid = $P_ID;
		    
		    
		    // Save in the file (this will remove any A)
		    // That do not belong to a location
		    fputs ($new_w2g_file_handle, $a->w2gString()."\n");
		}
		
		
		break;
		
	// General layer - fixed stuff at top of SVG
	case "G2":
		fputs ($new_w2g_file_handle, $line);
		fputs ($svg_file_handle, $parts[1]);
		break;
	// Save the PPM values
	case "PPM":
		fputs ($new_w2g_file_handle, $line);
		break;
		
} // End of Switch

} //end of while loop

// now write the objects that have to be drawn on top of the rooms 
// can't do this when actually reading in since wrong order 
// needed earlier to check if within polynomial/room

$index = 1;
$first_lift = 1;
$first_stairs = 1;

foreach ($verts as $vertex) {

	switch ($vertex->ass_t) {
		
		case "L":

				break;
		case "S":
				
				
				break;
		//entrances
		case "e":
			$next_v = $verts[$vertex->id+1];
			$x_normalised = (float)$vertex->x - $next_v->x;
			$y_normalised = (float)$next_v->y - $vertex->y; //take in to account y axis increase going down (ie 0,0 in top left corner)
			
			$icon_angle = rotation($x_normalised,$y_normalised);  //need to make average points centre and to left = -ve and below = -ve - hence reason for difference order (eg x= -ve,0 = 270deg)
			$vertex->angle = $icon_angle;
			break;
		//Bridge entrances
		case "be":
			$next_v = $verts[$vertex->id+1];
			$x_normalised = (float)$vertex->x - $next_v->x;
			$y_normalised = (float)$next_v->y - $vertex->y; //take in to account y axis increase going down (ie 0,0 in top left corner)
			
			$icon_angle = rotation($x_normalised,$y_normalised);  //need to make average points centre and to left = -ve and below = -ve - hence reason for difference order (eg x= -ve,0 = 270deg)
			$vertex->angle = $icon_angle;
			break;
	
	
	}
}



// Now list verticies in rewritten w2g file
foreach($verts as $vertex){
  // Do not save CID vertices! Since a location with the proper
  // link has been added (+a vertex for this location)
  //if (isCIDw2gType($vertex->ass_t)) continue;
  
  // Associate corridor vertex with door vertex (not room ID since 
  // there may be more than one door per room). 
  // C vertex always follows door vertex so index = -1
  if ($vertex->ass_t == "C") {
    $vertex->ass_id = $vertex->id-1;
  }
  
  
  $line_op = $vertex->w2gString()."\n";
  fputs ($new_w2g_file_handle, $line_op);
}


foreach ($LPs as $lp){
  fputs ($new_w2g_file_handle, $lp->w2gString()."\n");
}



/*

for ($i=0; $i<count($SVGLOC); $i++){
  fputs ($svg_file_handle, $SVGLOC[$i]);
  fputs ($new_w2g_file_handle, $W2GLOC[$i]);
}


*/



//close SVGPan pan/scale "viewport"
$line_op .= "</g>";

//close svg doc
$line_op .= "</svg>";
fputs ($svg_file_handle, $line_op);

fclose($w2g_file_handle);
fclose($svg_file_handle);

prtSubSec("Completed");
?>
