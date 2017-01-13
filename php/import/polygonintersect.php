 <?php
$new_paths = "..".PATH_SEPARATOR."../db";
set_include_path(get_include_path() . PATH_SEPARATOR . $new_paths);

 include_once('libmath.php');
 
//routine that returns the point of intersection betwen a point and a polgon
//returns the point coordinate - bascially snap to room boundary for door verticies 
//Will be used to find door intersection and angle towards guiderail (future)
//type = "D" or "E" - this is required to determine the corridor point for entrances and doors (default = Door)
function polygon_intersect_point($point, $poly_points,$door_type="D"){ 
global $svg_file_handle;

 // Split Points
  $polySides = count($poly_points);
 // echo "Sides = ".$polySides."<br/>";
  $x = $point->x;
  $y = $point->y;
  
  $empty_count = 0;
  $min_ray_length = 10000;
  // Split all poly_points in arrays
  for ($k=0; $k<$polySides; $k++) {
    // discart empty
    if (trim($poly_points[$k])=="") {
      $empty_count++;
      continue;
    }
    $tmp_pp = explode(',', $poly_points[$k]);
    $polyX[] = trim($tmp_pp[0]); 
    $polyY[] = trim($tmp_pp[1]);
  }
	  
  $polySides -= $empty_count;
  $result=false;
  $intercept=0;
  
  $SP1= $new_point = new Point;  // point 1 of the nearest wall
  $SP2 = $new_point = new Point; // point 2 of the nearest wall

  for ($i=0,$j=$polySides-1; $i<$polySides; $j=$i++){
	$P1 = new Point($polyX[$i],$polyY[$i]);
	$P2 = new Point($polyX[$j],$polyY[$j]);				
	$degree = 0.005;
	while ($degree <= 180) {
			$PPx = $x + 5*sin(deg2rad($degree));
			$PPy = $y + 5*cos(deg2rad($degree)); 
			//projected point from point (door)
			$PP = new Point($PPx, $PPy);
			$polygon_intersect = find_intersect_point($point,$PP,$P1,$P2);	
			if ($polygon_intersect!=null)   {
			//intersect function doesn't check that intersect point is on actual line (wall) since extrapolated
			//distance between wall intersect point to ends of wall must equal to wall length = test for good intersect
			$Dwall = Point::powDist($P1, $P2);
			$D1 = Point::powDist($polygon_intersect, $P1);
			$D2 = Point::powDist($polygon_intersect, $P2);
			$valid = false;
			if (($D1+$D2) <= ($Dwall+1)) $valid = true; //intersect point lies on wall - allow 1 for margin	
			if ($valid == true) {
			$ray_length = Point::powDist($point, $polygon_intersect);
			if ($ray_length < $min_ray_length) {
				$min_ray_length = $ray_length; //find shortest ray ie closest to room outline
				$new_point = new Point($polygon_intersect->x, $polygon_intersect->y);
				$SP1 = $P1;//record so can highlight nearest wall afterwards and calculate bearing 
				$SP2 = $P2;
			}
			}
			}
			$degree=$degree+1;
		}
};

//add a dashed orange circle around the new door point intersecting with nearest wall
$line_op = "<circle fill='none' stroke='#ea961c' stroke-width='0.3'  stroke-dasharray='0.5,1' stroke-linecap='round' cx='".$new_point->x."' cy='".$new_point->y."' r='2'/>\n";
fputs ($svg_file_handle, $line_op);

//Highlight the nearest walls that were used to link doors to - debug mode
//$line_op = "<line fill='#ffff00' stroke='#ea961c' stroke-width='1' x1='".$new_point->x."' y1='".$new_point->y."' x2='".$SP2->x."' y2='".$SP2->y."'/>\n";
//fputs ($svg_file_handle, $line_op);
//note the bearing is from the second point in this function!	 
$wall_bearing = Point::bearing($new_point,$SP1); //derive door angle from the wall bearing	from the intersect point
$door_bearing1 = floatval($wall_bearing)+90; 
//Try both + and -90 degrees and then test to see if inside or out of location polygon
$new_CP1 = new Point();
$new_CP1 = move_point ($new_point, $door_bearing1, 3);//call function that returns new coordinate based on bearing and start coordinate
//$line_op = "<circle fill='#cc0000' stroke='#FFFFFF' stroke-width='0.3' cx='".$new_CP1->x."' cy='".$new_CP1->y."' r='1'/>\n";
//$line_op .= "<text transform='matrix(1 0 0 1 ".$new_CP1->x." ".$new_CP1->y.")' font-family=\"'Arial'\" fill = \"#FFFFFF\" font-size=\"1.5\">....+90=".substr($door_bearing1,0,3)."</text>";
$door_bearing2 = floatval($wall_bearing)-90; 
$new_CP2 = new Point();
$new_CP2 = move_point ($new_point, $door_bearing2, 3);//call function that returns new coordinate based on bearing and start coordinate
//$line_op .= "<circle fill='#cc0000' stroke='#232323' stroke-width='0.3' cx='".$new_CP2->x."' cy='".$new_CP2->y."' r='1'/>\n";
//$line_op .= "<text transform='matrix(1 0 0 1 ".$new_CP2->x." ".$new_CP2->y.")' font-family=\"'Arial'\" fill = \"#FFFFFF\" font-size=\"1.5\">....-90=".substr($door_bearing2,0,3)."</text>";
//fputs ($svg_file_handle, $line_op);

//Test for inside or out
$result=insidePoints($new_CP1, $poly_points);
$inside_room = new Point();
$outside_room = new Point();
if ($result) {
  $inside_room = $new_CP1; 
  $outside_room = $new_CP2;
}
else {
  $inside_room = $new_CP2;
  $outside_room = $new_CP1;
}

//Now return the new corridor point based on the door type
//If door type = "E", the corridor vertex should be inside the location (BO = building outline)
//If door type = "D", the corridor vertex should be outside the location 

If ($door_type == "E") {
$new_CP1 = $inside_room;
}
else {
$new_CP1 = $outside_room;
}

//Show new corridor point on floorplan
$line_op = "<circle class = 'new_corridor' fill='#cc0000' stroke='#FFFFFF' stroke-width='0.3' cx='".$new_CP1->x."' cy='".$new_CP1->y."' r='1'/>\n";
fputs ($svg_file_handle, $line_op);
	
$new_points = array();
$new_points[0] = $new_point; //new door point
$new_points[1] = $new_CP1; 	//new corridor point
return $new_points;
}  
  
?>
  
 