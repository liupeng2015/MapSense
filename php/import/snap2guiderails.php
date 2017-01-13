<?php
include_once('importconsts.php');
include_once('Tools.php');
include_once('MathTools.php');
include_once('libmath.php');
include_once('libsvg.php');
include_once('polygonintersect.php');

$max_door_to_corridor = 6000; //length not sqrt disatnce


//remove hashes - not needed and messing up program!
// The following is temporary and will be over-written by w2g2svg.php
$background_colour = "#C1E5E8";

// These SHOULD BE PARAMETERS so the final w2g file contains all the 
// polygon information including the colors (TODO: talk?!)
//$polygon_line_thickness = 0.2;
//$polygon_fill = "#2981C4"; //GW "#D0D391";//CW  "#94C2E3";		//MB:"#F5E793";//#6699CC";
//$polygon_colour = "#C1E5E8"; //"#E2DFCF"; //GW"#EAEACF"; //GW"#D5E7F0"; //MB:"#FDFADA";


error_reporting(E_ALL);


// Load Configuration
prtSubSec("Processing");
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

$fbname=basename($fname,$GEO_EXT);
$w2g_file = $fname;

$svg_file = $PROJECT_DIR."/".$building.$floor.$room."/".$fbname."_snap.svg";

//need to define this here since if there are no edges, then this is never set and causes a problem later on line 502
$last_edge = 0;
	
//Open up file and start reading

$w2g_file_handle = fopen($w2g_file, "r");
// TODO: Check files opened...
$svg_file_handle = fopen($svg_file, "w");

// Objects
$txts = array();
$perimeter = array(); //stuff to be drawn first - ie background
$generals = array(); //stuff to be drawn last - ie on top
$edges = array();
$verts = array();
$locs = array();
// Vertices start/end of guiderails
$vgs = array();
// Guiderails (vg1 - vg2)
$guides = array();
// Layers
$LPs = array();
$aliases=array();
$PPM="";
//zz3
// Start Parsing
while (!feof($w2g_file_handle)) {
        $line = fgets($w2g_file_handle);
        $parts = explode(';', $line);
        if (trim($line) == '') continue;

	switch ($parts[0]) {
	
	  case "p":
	    $w2g_project=$parts[1];
	    $w2g_building=$parts[2];
	    $w2g_floor=$parts[3];
	    printSubSec( "Going for: ".$w2g_project.$w2g_building.$w2g_floor );
	    break;
	    
	  case "vb":
	    $width = $parts[1];
	    $height = $parts[2];
	    $width_png = $parts[3];
	    $height_png = $parts[4];
	    $line_op = "<?xml version='1.0' encoding='utf-8'?>\n";
	    $line_op .= "<!DOCTYPE svg PUBLIC '-//W3C//DTD SVG 1.1//EN' 'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd'>\n";
	    $line_op .="<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'\n";
	    $line_op .="width='100%' height='100%' viewBox='0 0 ".$width." ".$height."' enable-background='new 0 0 ".$width." ".$height."'\n";
	    $line_op .= "xml:space='preserve'>\n";
	    $line_op .= "<defs>\n";
	    
	    $line_op .= "<script  xlink:href='http://www.wai2go.co.uk/RL3/admin/lib/js/click_svg_element.js'/>\n";
	    $line_op .= "<script  xlink:href='http://www.wai2go.co.uk/RL3/admin/lib/js/jquery-1.7.1.min.js'/>\n";
	    $line_op .= "<script  xlink:href='http://www.wai2go.co.uk/RL3/admin/lib/js/SVGPan_admin.js'/>\n";
	    $line_op .= "</defs>\n";
	    
	    //svg pan and zoom - viewport
	    $line_op .= "<g id = 'viewport'>\n";
	    //background - user controlled from background colour variable
	    //$line_op .= "<polygon id='bg_color' fill='".$background_colour."' stroke='".$background_colour."' points='0,0 0,".$height." ".$width.",".$height." ".$width.",0'/>\n";
	    fputs ($svg_file_handle, $line_op);
	    break;
	    
	  //text values : t;1;0.9941;0.1089;-0.1089;0.9941;413.3789;826.5752;Lift;font size;loc assoc
	  case "t":
	    $t = w2gText::getFromParts($parts);
	    $txts[$parts[1]] = $t;
	    $tmp_mat = str_replace(";", ' ', $t->mat);
	    $line_op = $t->svgString()."\n";
	    fputs ($svg_file_handle, $line_op);
	    break;
	
	//Perimeter layer - fixed stuff to be drawn first - background stuff
	
	case "G1":
	    array_push($perimeter,$line);
	    fputs ($svg_file_handle, $parts[1]);
	    break;
	
	//General layer - fixed stuff to be drawn last - on top of locations layer - fixed text etc
	
	case "G2":
	    array_push($generals,$line);
	    fputs ($svg_file_handle, $parts[1]);
	    break;

	//Edges - load into an array --> e;1;15;16;N;;53.2853;type
	case "e":
	  $edges[$parts[1]] = w2gEdge::getFromParts($parts);
	  
	  // KEN FIXME: It seems edges have no type...
	  if ($edges[$parts[1]]->type == "G") { //keep track of how many guidelines there are  
		  $last_guiderail = $parts[1];
	  }	
	  $last_edge = $parts[1];
	  break;
	  
	//Verticies - load into an array since used to create paths from edge points		
	case "v":
	  $v = w2gVertex::getFromParts($parts);
	  // vertices indexed by ID
	  $verts[$v->id] = $v;
	  break;

	case "vg":
	  $vg = w2gGuideVertex::getFromParts($parts);
	  $vgs[$vg->id]= $vg;
	  break;

	case "GR":
	  $gr = w2gGuideRail::getFromParts($parts);
	  $guides[$gr->id] = $gr;
	  break;
			
	//locations ---> l;22;Great Bentley Ward;399.5;1410.5;353.445,1300.48 446.446,1300.48 446.446,1521.48 353.445,1521.48; fill colour;line colour,line_thickness;layer;loc_text(comma delimited)
	case "l":
	  $l = w2gLocation::getFromParts($parts);
	  //zz2
	  $locs[$l->id] = $l;
	  $line_op= $l->svgString();
	  fputs ($svg_file_handle, $line_op);
	  break;
	case "LP":
	  // UrbaN: Layer Points Must go back to be imported from w2g2db!
	  array_push($LPs, w2gLayerPoint::getFromParts($parts));
	  break;
	case "A":
	    $aliases[] = w2gAlias::getFromParts($parts);
	    break;
	// Save the PPM values
	case "PPM":
	    $PPM = $line;
	    break;
	default:
	  prtE("MISSED LINE: '$line'");
      }
} // End of Parsing...


//Draw guidelines now that all the verticies are loaded
foreach ($guides as $g) {
  
  //Draw guiderail edges - temporary
  $vg_start = $vgs[$g->v1];
  $vg_end = $vgs[$g->v2];

  $line_op = "<line id = 'P".$g->id."' fill='none' stroke='".$orange."' stroke-linecap='round' stroke-width='1' stroke-miterlimit='12' x1='".$vg_start->x."' y1='".$vg_start->y."' x2='".$vg_end->x."' y2='".$vg_end->y."'/>\n";
  fputs ($svg_file_handle, $line_op);

}

// Draw edges already found - showing location to door edges - highlights anomolies are those missed
foreach ($edges as $e) {
  
//Draw guiderail edges - temporary
  $X1=$verts[$e->v1]->x;
  $Y1=$verts[$e->v1]->y;
  $X2=$verts[$e->v2]->x;
  $Y2=$verts[$e->v2]->y;
  $line_op = "<line id = 'P".$e->id."' fill='none' stroke='#E30513' stroke-linecap='round' stroke-width='0.2' stroke-miterlimit='12' x1='".$X1."' y1='".$Y1."' x2='".$X2."' y2='".$Y2."'/>\n";
  fputs ($svg_file_handle, $line_op);			
}

//*****************************************************************************
//Snap doors to room outline intersections - uses polyintersect.php 
//Take doors associated with a location and snap to the location boundary
//*****************************************************************************
$llpoints= array();
$new_points = array();

foreach ($locs as $ll) {
	
	//align doors for locations and entrances for the building outline = loc with CID type = "BO" = "Outline"
	if ($ll->layer != "BO") {
		//echo  "Aligning doors for location: ".$ll->name."<br/>";
		//echo  "Location type: ".$ll->layer."<br/>";
		//echo  "Name:".$ll->name."<br/>";
		//echo  "Points:".$ll->points."<br/>";
		$polyPoints = explode(' ', $ll->points);
		foreach ($verts as $v) { 
			//Process for each door within the room - inlcude Virtual doors (vdoors)	
			if (($v->ass_t =="D" || $v->ass_t =="VD" ) && ($v->ass_id == $ll->id)) {
				//echo "Match<br/>";
				$D = new Point(floatval($v->x),floatval($v->y)); //Door point
				//echo ("Door asoc id:".$v->ass_id."<br/>");
				//echo ("Door angle:".$v->angle."<br/>");
				//if ($->angle != null) 
				if ($v->ass_t =="D") $door_type = "D";
				if ($v->ass_t =="VD") $door_type = "VD";
				
				//function returns $new_points[0] new door point $newpoints[1] new corridor point
				$new_points = polygon_intersect_point($D,$polyPoints, $door_type); //third parameter = angle; if missing will find nearest wall intersect 
				if ($new_points[0] != null) {
					$verts[$v->id]->x = $new_points[0]->x;  //new door point
					$verts[$v->id]->y = $new_points[0]->y;
					$verts[$v->id+1]->x = $new_points[1]->x;//new corridor point
					$verts[$v->id+1]->y = $new_points[1]->y;
					$door_angle = Point::bearing($new_points[0],$new_points[1]);
					$verts[$v->id]->angle = $door_angle;
					$verts[$v->id+1]->angle = $door_angle;					
				}
			}
		}
	}	
	if ($ll->layer == "BO") {
		//echo  "****Aligning entrances for building outline: ".$ll->name."<br/>";
		//echo  "Location type: ".$ll->layer."<br/>";
		//echo  "Name:".$ll->name."<br/>";
		//echo  "Points:".$ll->points."<br/>";
		$polyPoints = explode(' ', $ll->points);
		foreach ($verts as $v) { 
			//Process for each door within the room	
			if (($v->ass_t =="e") || ($v->ass_t =="be")) {
				//echo "Match<br/>";
				//Don't associate entrances with a perimeter/outline in the geofence routine - do it here 
				//since floorplans may have more than one perimeter
				
				$result=insidePoints($v, $polyPoints);
				// Check result before moving on
				if ($result != true) continue;	
				$v->ass_id = $ll->id; //update the association for the entrance
				
				$D = new Point(floatval($v->x),floatval($v->y)); //Door point
				echo ("Door id:".$v->id." assoc with location: ".$ll->id."<br/>");
				
				//$angle = $v->angle;
				//if ($->angle != null) 
				
				$door_type = "E";
				//if set to "E" then following function snaps to perimeter
				$new_points = polygon_intersect_point($D,$polyPoints,$door_type); 
				
				if ($new_points[0] != null) {
					$verts[$v->id]->x = $new_points[0]->x;
					$verts[$v->id]->y = $new_points[0]->y;
					$verts[$v->id+1]->x = $new_points[1]->x;//corridor points
					$verts[$v->id+1]->y = $new_points[1]->y;
					//echo "*********************<br/>";
					//echo "New corridor points: x = ".$new_points[1]->x.", y = ".$new_points[1]->y."<br/>";
					//echo ("Current entrance angle:".$v->angle."<br/>");
					$door_angle = Point::bearing($new_points[0],$new_points[1]);
		
					$verts[$v->id]->angle = $door_angle;
					$verts[$v->id+1]->angle = $door_angle;					
					//echo "New entrance angle: ".$door_angle."<br/>";
					//echo "*********************<br/>";
					
					/*
					if ($new_points[2] >180) {
						$verts[$v->id]->angle = $new_points[2]-180;//Need to flip due to nature of entrances. Always adding was causing a problem
						$verts[$v->id+1]->angle = $new_points[2]-180;//change the corridor angle also	
					}
					else {
						$verts[$v->id]->angle = $new_points[2]+180;
						$verts[$v->id+1]->angle = $new_points[2]+180;	
					}
					*/
					
					
				}
			}
		}
	}	
}

//*****************************************************************
//****************** GUIDERAIL INTERSECTION ***********************
//*****************************************************************
//Create a vertex at each gridline intersection

foreach ($guides as $g1) {

  $vg_start = $vgs[$g1->v1];
  $vg_end = $vgs[$g1->v2];

  $guiderail_length = Point::powDist($vg_start, $vg_end);
  foreach ($guides as $g) {
    if ($g->id == $g1->id) continue;
		
    //echo "Testing against Guiderail: ".$g;
    $vg_start1 = $vgs[$g->v1];
    $vg_end1 = $vgs[$g->v2];
  
    $guiderail_length1 = Point::powDist($vg_start1, $vg_end1);
    
    $corridor_point = find_intersect_point($vg_start,$vg_end,$vg_start1,$vg_end1);
    if ($corridor_point==null) continue;
    if (($corridor_point->x <= 0) || ($corridor_point->y <= 0)) continue;
    
    $inter_to_s = Point::powDist($corridor_point, $vg_start);
    $inter_to_e = Point::powDist($corridor_point, $vg_end);
    
    //Caclulate distance from corridor vertex to end of guideline
    $inter_to_s1 = Point::powDist($corridor_point, $vg_start1);
    $inter_to_e1 = Point::powDist($corridor_point, $vg_end1);
    
    //for two guidelines to intersect, the distance from each end to the intersect point
    // = length of each of the guiderails (add a little margin)
    //sum of distances must equal the length of the guideline - add a pixel for safe measure - rounding issues
    if ( (($inter_to_s1 + $inter_to_e1) <= ($guiderail_length1+1)) && 
         (($inter_to_s + $inter_to_e) <= ($guiderail_length+1)) ) {
         
      //echo "Match!<br>";
      $new_C_x = floatval($corridor_point->x);		
      $new_C_y = floatval($corridor_point->y);
      
      // Note: has to be a while loop not a for loop since MUST stop when first match found, otherwise will find LAST and WRONG intersection point somewhere else! @@
      //flag to show that this is not a new vertex to save duplication
      $already_done = false; 
      foreach ($verts as $v) {
	if ((abs($v->x - $new_C_x)<0.01) && (abs($v->y - $new_C_y))<0.01) {
		$already_done = true;
		// Ensure the vertex bellong to these GR
		// (It may be a 3rd one)
		$v->guides[$g->id]=true;
		$v->guides[$g1->id]=true;
		break;
	}
      }

      if ($already_done) continue;

      // Add a circle on each intersection
      $line_op = "<circle id = 'G1_??' fill='#F59D44' stroke='#FFFFFF' stroke-width='0.1' stroke-linecap='round' cx='".$new_C_x."' cy='".$new_C_y."' r='1.5'/>\n";
      fputs ($svg_file_handle, $line_op);
      
      // Add a GG vertex
      // continue index from previously read in verticies
      $v_new = new w2gVertex($new_C_x, $new_C_y);
      // GG = Guiderail to guiderail intersection point
      $v_new->ass_t = "GG";
      $v_new->id = count($verts)+1;
      $verts[$v_new->id] = $v_new;
      
      // use this to show which guiderail the point is associated with -
      // saves having to add another vertex which will cause duplication
      $v_new->guides[$g->id]=true;
      $v_new->guides[$g1->id]=true;
      // Save
      $verts[$v_new->id] = $v_new;
    }

  } // End of Inner Loop
} // End of Outer Loop

// Add icons for verticies that are layers and snap corridor verticies to guiderail
foreach ($verts as $v) { 

	// SVG Vertex Styles
	$svg_vtype1 = "<circle id = 'D".$v->id."' fill='#00ee00' stroke='#000000' stroke-width='0.1' stroke-linecap='round' cx='".$v->x."' cy='".$v->y."' r='1'/>\n";
	
	$svg_vtype2 = "<circle id = 'D".$v->id."' fill='#FFFFFF' stroke='#000000' stroke-width='0.1' stroke-linecap='round' cx='".$v->x."' cy='".$v->y."' r='1'/>\n";
	
	$svg_vtype3 = "<circle id = 'D".$v->id."' fill='#000000' stroke='#FFFFFF' stroke-width='0.1' stroke-linecap='round' cx='".$v->x."' cy='".$v->y."' r='0.5'/>\n";
	
	$svg_vtype4 = "<circle id = 'D".$v->id."' fill='#FFFFFF' stroke='#000000' stroke-width='0.1' stroke-linecap='round' cx='".$v->x."' cy='".$v->y."' r='0.8'/>\n";
	$svg_vtype5 = "<circle id = 'VD".$v->id."' fill='#DD6512' stroke='#000000' stroke-width='0.1' stroke-linecap='round' cx='".$v->x."' cy='".$v->y."' r='0.8'/>\n";
	$svg_vtype6 = "<circle id = 'VD".$v->id."' fill='#DD6512' stroke='#000000' stroke-width='0.1' stroke-linecap='round' cx='".$v->x."' cy='".$v->y."' r='1.4'/>\n";
	
	if ($v->ass_t == "L")       fputs ($svg_file_handle, $svg_vtype1);
	else if ($v->ass_t == "S")  fputs ($svg_file_handle, $svg_vtype1);
	else if ($v->ass_t == "TM") fputs ($svg_file_handle, $svg_vtype2);
	else if ($v->ass_t == "TF") fputs ($svg_file_handle, $svg_vtype2);
	else if ($v->ass_t == "TD") fputs ($svg_file_handle, $svg_vtype2);
	else if ($v->ass_t == "TO") fputs ($svg_file_handle, $svg_vtype2); 
	else if ($v->ass_t == "D")  fputs ($svg_file_handle, $svg_vtype3);	
	else if ($v->ass_t == "e")  fputs ($svg_file_handle, $svg_vtype4);
	else if ($v->ass_t == "be")  fputs ($svg_file_handle, $svg_vtype6);
	else if ($v->ass_t == "VD")  fputs ($svg_file_handle, $svg_vtype5);
	//*****************************************************************************************************************
	//********** CORRIDOR VERTEX SNAPPING TO GUIDERAILS ***************************************************************
	//Project door entrance onto guiderails to created corridor vertex and edge from corridor to door entrance vertex
	//*****************************************************************************************************************			
	else if ($v->ass_t == "C"){

	  $shortest_distance = 100000;
	  // Current/Corridor Point: put into a (x,y) format
	  $CP = $v;
	  
	  // Corridor has an association ID equal to the ID of the door that it is connected to
	  $DP = $verts[$v->ass_id];
	  // Door Point put into a (x,y) format
	  //$DP = $door_v->x.",".$door_v->y;
	  //$DP = $door_v;
	  
	  
	  // Loop through guiderails and find the closest GR 
	  // to the CP (corr. point)
	  foreach ($guides as $g){

		  // Start and End of Guiderail...
		  $G1=$vgs[$g->v1];
		  $G2=$vgs[$g->v2];
		  
		  //to only find intersections along the guideline, need to first calculate length of guideline
		  $guiderail_length =  Point::powDist($G1, $G2);
		  
		  // Intersection Point
		  $corridor_point = find_intersect_point($DP,$CP,$G1,$G2);
		  
		  // Check for parallel lines...
		  if ($corridor_point==null) continue;
		  // Only consider +ve intersection points
		  if (($corridor_point->x <= 0) || ($corridor_point->y <= 0)) continue;
		
/*****************************************************************************/
		
		  // Ensure that the intersection point is at the correct direction
		  // by comparing bearings
		  $int_bearing = Point::bearing($DP, $corridor_point); 
		  if (abs($int_bearing - $DP->angle)>45){
		    // Commented out since it seems working
		    //prtW("Skipping intersection because of bearing mismatch=".$int_bearing." door->angle=".$DP->angle);
		    continue;
		  }
		  
		  $DP_to_inter =  Point::powDist($corridor_point, $DP);
		  
		  // Distance from intersection vertex to start of guideline
		  $inter_to_G1 =  Point::powDist($corridor_point, $G1);
		  // Distance from intersection vertex to end of guideline
		  $inter_to_G2 =  Point::powDist($corridor_point, $G2);
		  
		  
		  // Sum of distances must equal the length of the guideline 
		  if (($inter_to_G1 + $inter_to_G2) <= ($guiderail_length+1)){
			  if ($DP_to_inter < $shortest_distance){ 
				  $guiderail_ID = $g->id; 
				  $shortest_distance = $DP_to_inter;
				  $new_C_x = floatval($corridor_point->x);
				  $new_C_y = floatval($corridor_point->y);
			  }
		  }
	  }
	  
	  // Update vertex coordinates
	  // Put a restriction on the length of the door to corridor edge - 
	  // don't want it across the floor!
	  // Remember that this the ^2 - no sqrt performed only pow + pow 
	  if ($shortest_distance < $max_door_to_corridor) {
	    $v->x= $new_C_x;
	    $v->y= $new_C_y;
	    // Mark point as having been snapped to guidline
	    $v->ass_t = "SC";
	    // Set assoc to guiderail ID
	    $v->ass_id = $guiderail_ID;
	    $v->guides[$guiderail_ID]=true;

	    // Connect Door to Corridor Point
	    $last_edge = $last_edge +1;
	    $e = new w2gEdge($CP->id,$DP->id);
	    $e->id=$last_edge;
	    $e->stairs = "";
	    $e->type="";
	    $e->length=Point::dist($CP, $DP);
	    // Save
	    $edges[$last_edge]=$e;

	    // Draw SVG...
	    // The line $CP->$DP (black)
	    $line_op = "<line id = 'P".$e->id."' fill='none' stroke='#FFFFFF' stroke-linecap='round' stroke-width='0.5' stroke-miterlimit='12' x1='".$new_C_x."' y1='".$new_C_y."' x2='".$DP->x."' y2='".$DP->y."'/>\n";
	    fputs ($svg_file_handle, $line_op);
	    
	    // Blue circle on CP
	    $line_op = "<circle id = 'CV".$v->id."' fill='#6699CC' stroke='#FFFFFF' stroke-width='0.1' stroke-linecap='round' cx='".$new_C_x."' cy='".$new_C_y."' r='0.5'/>\n";
	    fputs ($svg_file_handle, $line_op);
	  }
	}
}		

//**********************
//joining THE DOTS!
//**********************

// Now join up the corridor verticies lying on the guiderail including the guideline intersection points
foreach ($guides as $g){
  $G = $vgs[$g->v1];
  $shortest_distance = 100000;
  
  //Step 1: Find first vertex closest to one end of the guiderail - 
  //        use this as the first reference vertex: ref_vertex_ID
  $ref_vertex = null;
  foreach ($verts as $v) {   
    //"S" means snapped corridor point
    if (($v->ass_t == "SC") && ($v->ass_id == $g->id)){  
      $distance = Point::powDist($G, $v);
      if ($distance < $shortest_distance){ 
	$ref_vertex = $v; 
	$shortest_distance = $distance;
      }
    }
    //"GG" means grid intersection vertex
    else if (($v->ass_t == "GG") && isset($v->guides[$g->id]) ){
      $distance = Point::powDist($G, $v);
      if ($distance < $shortest_distance){ 
	$ref_vertex = $v; 
	$shortest_distance = $distance;
      }
    }
  } // End of Vertex loop
  
  if ($ref_vertex==null){
    prtE("No reference vertex for guiderail (non fatal)");
    continue;
  }

  //Step 2: Now we have the start/reference vertex, find the next 
  //        vertex along the guiderail closest to this one
  $more_verticies = true; //use as a flag to show when there are no more verticies to check per guiderail
  $match_guide = 0;
  while ($more_verticies) {
	$shortest_distance = 100000;
	
	// Exclude considered vertices so you do not move to the 
	// previous vertex...
	if ($ref_vertex->ass_t == "SC"){
	  // Remove the reference vertex from the pool so it doesn't find itself everytime "J" = "joined" only if not a guiderail
	  $ref_vertex->ass_t = "JC";
	}
	else if (($ref_vertex->ass_t == "GG")) {
	  unset($ref_vertex->guides[$g->id]);
	}
	
	// Inner vertex loop
	foreach ($verts as $v) {
		// Skip ourselvs
		if ($v->id == $ref_vertex->id) continue;
		
		//select verticies associated with each guiderail in turn
		
		//only consider snapped corridor vericies that lie on considered guiderail 
		if (($v->ass_t == "SC") && ($v->ass_id == $g->id)){
			$distance = $distance = Point::powDist($ref_vertex, $v);
			// if this has the shortest distance, then tentatively make it the next vertex to be considered
			if ($distance < $shortest_distance){ 
				$next_vertex = $v; 
				$shortest_distance = $distance;
				$match_guide = 0;  //flag to show that this is a SC point	
			}
		}
		//Now consider the intersection of guidrails - "GG" points with assocID_gx variables
		//Consider first "GG" vertex associated with one of its intersecting guiderails
		else if (($v->ass_t == "GG") && isset($v->guides[$g->id])){
			$distance = $distance = Point::powDist($ref_vertex, $v);
			if ($distance < $shortest_distance){ 
				$next_vertex = $v;
				$shortest_distance = $distance;
				// remove this guiderail from the vertex
				$match_guide=1;
			}
		}
					
	}
	

	// Break loop if none was found
	if ($shortest_distance == 100000)  break;

	//start adding new edges since don't exits already
	$last_edge = $last_edge +1;
	$e = new w2gEdge($ref_vertex->id,$next_vertex->id);
	$e->id=$last_edge;
	$e->type="c";
	$e->length=Point::dist($ref_vertex, $next_vertex);
	
	// Save
	$edges[$last_edge]=$e;

	
	if ($match_guide == 0){ //black lines
	  $line_op = "<line id = 'P".$e->id."' fill='none' stroke='#000000' stroke-linecap='round' stroke-width='0.5' stroke-miterlimit='12' x1='".$ref_vertex->x."' y1='".$ref_vertex->y."' x2='".$next_vertex->x."' y2='".$next_vertex->y."'/>\n";
	  fputs ($svg_file_handle, $line_op);	
	}
	else if ($match_guide == 1) {//dark blue lines 
	  $line_op = "<line id = 'P".$e->id."' fill='none' stroke='#094CB7' stroke-linecap='round' stroke-width='0.5' stroke-miterlimit='12' x1='".$ref_vertex->x."' y1='".$ref_vertex->y."' x2='".$next_vertex->x."' y2='".$next_vertex->y."'/>\n";
	  fputs ($svg_file_handle, $line_op);	
	}
	
	$more_verticies = true;
	// Next cycle now begins using the next vertex as the reference vertex
	$ref_vertex = $next_vertex;

  }
				
}


// Location touch overlay - also contains the location name and 
// x,y coordinates for the info_box (to save accessing db)
foreach ($locs as $l) { 
  //location vertex (red circle)
  $line_op = "<circle id = 'D".$l->id."' fill='#E30513' stroke='#FFFFFF' stroke-width='0.1' stroke-linecap='round' cx='".$l->x."' cy='".$l->y."' r='0.5'/>\n";
 // $name = $l->name;
 // $name = str_replace($name,"&amp;","&");
  //$name = str_replace($name,"'","&quot;");
 // $line_op .= "<polygon id = 'O".$l->id."' location = '".$name."' P_x = '".$l->x."' P_y = '".$l->y."' display='inline' fill-opacity='0.001' fill='#FFFFFF' stroke='none' points='".$l->points."'/>\n";
  //fputs ($svg_file_handle, $line_op);
}

//close SVGPan pan/scale "viewport"
$line_op .= "</g>";
//close svg doc
$line_op .= "</svg>";
fputs ($svg_file_handle, $line_op);

fclose($w2g_file_handle);
fclose($svg_file_handle);


// TODO: Move into the switch on "G" so we do not keep in memory all 
// the general layer
// Remember to add rewriting of W2G with new paths found from guiderail fitting
$w2g_file = $PROJECT_DIR."/".$building.$floor.$room."/".$fbname.$SNAP_EXT;
$w2g_file_handle = fopen($w2g_file, "w");

//Project details
$line_op = "p;".$project.";".$building.";".$floor.$room."\n"; 
fputs ($w2g_file_handle, $line_op);

//Viewbox
$line_op = "vb;".$width.";".$height.";".$width_png.";".$height_png."\n";
fputs ($w2g_file_handle, $line_op);


// PPM
if ($PPM!="") {
  fputs ($w2g_file_handle, $PPM);
}


//Perimeter layer

foreach ($perimeter as $p){
  fputs ($w2g_file_handle, $p);
}

//top general layer, G2 (old "general")

foreach ($generals as $g){
  fputs ($w2g_file_handle, $g);
}

	
//locations
foreach ($locs as $l){
	// Urban: replace all colors... these will eventually be parameters and the CAD will be independent 
	// from any coloring crap...
	if ($l->fill == "" || $l->fill == "none") $l->fill = $polygon_fill;
	if ($l->color == "" || $l->color == "none") $l->color = $polygon_stroke;
	if ($l->stroke == "") $l->stroke = $polygon_stroke_w;
	$line_op = $l->w2gString()."\n"; //KG added "\n"
	fputs ($w2g_file_handle, $line_op);
	//Write text associated with location
	//zz1 file_put_contents("/var/www/RL3/php.log","l=$line_op\n",FILE_APPEND);
	foreach ($txts as $t) {	
	  // URban: SEE GEOF.PHP (Search: "not ALL texts")
	  if ($t->loc=="" || $t->loc!=$l->id) continue; //KG was == rather than !=
	  $line_op = $t->w2gString()."\n"; 
	  fputs ($w2g_file_handle, $line_op);
	}
	
}  
//exit;	
//vertex
foreach ($verts as $v){
	//If door vertex, then calculate door angles based on angle of edge between door and corridor verticies
	if ($v->ass_t == "D") {
		$next = $verts[$v->id+1];
		$x_normalised = (float)$v->x-$next->x;
		$y_normalised = (float)$next->y-$v->y; //take in to account y axis increase going down (ie 0,0 in top left corner)
		$icon_angle = rotation($x_normalised,$y_normalised);  //need to make average points centre and to left = -ve and below = -ve - hence reason for difference order (eg x= -ve,0 = 270deg)
		$v->angle = $icon_angle;
	}
	// KEN FIXME: Changed the line below... seems wrong! v:id:x:y what is the V_matrix
	$line_op = $v->w2gString()."\n";
	fputs ($w2g_file_handle, $line_op);
}  

foreach ($edges as $e){
	//calculate distance of edge only if there isn't already a length - this is to allow overriding (eg length from door - location verticies is set to be high to prevent routing "cut-through" the room 
	//png is 4x the size of the svg - keep the same so that can edit the png version and distances stay the same 
	//????
	$line_op = $e->w2gString()."\n";
	fputs ($w2g_file_handle, $line_op);
}

// Layer Points Back...
// TODO: put on the svg too,,.
foreach($LPs as $lp){
  $line_op = $lp->w2gString()."\n";
  fputs ($w2g_file_handle, $line_op);
}

// Aliases
foreach($aliases as $a){
  $line_op = $a->w2gString()."\n";
  fputs ($w2g_file_handle, $line_op);
}


fclose($w2g_file_handle);

prtSubSec( "Processed: Locations: ".count($locs)." Edges: ".count($edges)." Verticies: ".count($verts));

?>