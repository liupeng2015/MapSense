<?php

$EDGE_INTER_THRES=0.01; // 1*10^-9

function toDeg($rad){
  return (180.0*$rad)/M_PI;
}


function toRad($deg){
  return ($deg*M_PI)/180;
}


function mean($arr)
{
   if (!is_array($arr)) return false;
   return array_sum($arr)/count($arr);
}

/**
 * Scale a DB point (having lat,lon)
 * If SY is ommited, SX is used
 */
function scalePoint($v, $sx, $sy=-1000){

  // Check n replace SY
  if ($sy==-1000) $sy=$sx;
  
  return array(
	  "lat"=>($v->get_lat() * $sx),
	  "lon"=>($v->get_lon() * $sy)
	      );
}
function scalePointA($v, $sx, $sy=-1000){

  // Check n replace SY
  if ($sy==-1000) $sy=$sx;
  
  return array(
	  "lat"=>($v["lat"] * $sx),
	  "lon"=>($v["lon"] * $sy)
	      );
}

/**
 * Scale a DB point (having lat,lon)
 * If SY is ommited, SX is used
 */
function scaleOffsetPoint($v, $ox, $oy, $sx, $sy=-1000){

  // Check n replace SY
  if ($sy==-1000) $sy=$sx;
  
  return array(
	  "lat"=>($v->get_lat() * $sx) + $ox,
	  "lon"=>($v->get_lon() * $sy) + $oy
	      );
}


function scaleOffsetPointA($v, $ox, $oy, $sx, $sy=-1000){

  // Check n replace SY
  if ($sy==-1000) $sy=$sx;
  
  return array(
	  "lat"=>($v["lat"] * $sx) + $ox,
	  "lon"=>($v["lon"] * $sy) + $oy
	      );
}


function scaleOffsetPointsStr($l_points, $ox, $oy, $sx, $sy=-1000){
  $points = explode("\n",trim($l_points));
  $scaled="";
  
  // Check n replace SY
  if ($sy==-1000) $sy=$sx;
  
  foreach ($points as $p) {
    $parts = explode(",", $p);
    if (count($parts)<2) continue;
    $Px= floatval($parts[0]);
    $Py= floatval($parts[1]);
    
    if (($Px != 0) && ($Py != 0)) {
      $svg_x = ($Px*$sx + $ox);
      $svg_y = ($Py*$sy + $oy);
      

      $scaled .= $svg_x.",".$svg_y."\n"; //exclude any "0" points
    }
  }
  
  return $scaled;
}

function getMaxPointsStr($l_points){
  $points = explode("\n",trim($l_points));
  $scaled="";
  
  $max=array();
  $max["x"]=-1000;
  $max["y"]=-1000;
  
  foreach ($points as $p) {
    $parts = explode(",", $p);
    if (count($parts)<2) continue;
    $Px= floatval($parts[0]);
    $Py= floatval($parts[1]);
    
    if ($Px>$max["x"]) $max["x"]=$Px;
    if ($Py>$max["y"]) $max["y"]=$Py;
  }
  
  return $max;
}


/**
 * Return the angle between lines v1 - v2 and 
 * v3 - v4 in RADs
 */
function getAngleOfLines_rad($v1,$v2,$v3,$v4){
  $x1 = $v1->get_lat();
  $y1 = $v1->get_lon();
  
  $x2 = $v2->get_lat();
  $y2 = $v2->get_lon();
  
  $x3 = $v3->get_lat();
  $y3 = $v3->get_lon();
 
  $x4 = $v4->get_lat();
  $y4 = $v4->get_lon();
 
  // Get the line!
  $m1 = ($y2 - $y1)/($x2 - $x1);
  //$m1 = tan($a1);
  
  $m2 = ($y4 - $y3)/($x4 - $x3);
  //$m2 = tan($a2);
  
  // Corner
  $f = atan(($m1-$m2)/(1 + $m1*$m2));
  
  return $f;
}


/**
 * Return the angle between vectors A=v1v2 and 
 * B=v3v4 in RADs
 */
function getAngleOfVectors_rad($v1,$v2,$v3,$v4){
  $x1 = $v1->get_lat();
  $y1 = -$v1->get_lon(); // to cartesian
  
  $x2 = $v2->get_lat();
  $y2 = -$v2->get_lon();
  
  $x3 = $v3->get_lat();
  $y3 = -$v3->get_lon();
  
  $x4 = $v4->get_lat();
  $y4 = -$v4->get_lon();
 
  // Vectors
  $Ax = $x2 - $x1;
  $Ay = $y2 - $y1;
  
  $Bx = $x4 - $x3;
  $By = $y4 - $y3;
  
  $cos_f = $Ax*$Bx + $Ay*$By;
  // Avoid division with 0
  $div=sqrt(pow($Ax,2)+pow($Ay,2))*sqrt(pow($Bx,2)+pow($By,2));
  if ($div == 0) return 0;
  $cos_f /=$div;
  
  $f = acos($cos_f);
  if (crossProd($v1,$v2,$v3,$v4) < 0) $f=-$f;
  
  return $f;
}



/**
 * Cross-Product
 */
function crossProd($v1,$v2,$v3,$v4){
  $x1 = $v1->get_lat();
  $y1 = -$v1->get_lon(); // to cartesian
  
  $x2 = $v2->get_lat();
  $y2 = -$v2->get_lon();
  
  $x3 = $v3->get_lat();
  $y3 = -$v3->get_lon();
  
  $x4 = $v4->get_lat();
  $y4 = -$v4->get_lon();
 
  // Vectors
  $Ax = $x2 - $x1;
  $Ay = $y2 - $y1;
  
  $Bx = $x4 - $x3;
  $By = $y4 - $y3;
  
  return ($Ax*$By - $Bx*$Ay);

}

/**
 * Distance between 2 objects given that they have lat,lon
 */
function getDistance($p1, $p2){
  $dist = sqrt(pow($p1->lat-$p2->lat, 2) + pow($p1->lon-$p2->lon, 2));
  return $dist;
}

/**
 * Distance between an object and an array
 */
function getDistanceOA($p1, $p2){
  $dist = sqrt(pow($p1->lat-$p2["lat"], 2) + pow($p1->lon-$p2["lon"], 2));
  return $dist;
}

/**
 * Distance between 2 arrays
 */
function getDistanceAA($p1, $p2){
  if (isset($p1["x"])) $dist = sqrt(pow($p1["x"]-$p2["x"], 2) + pow($p1["y"]-$p2["y"], 2));
  else $dist = sqrt(pow($p1["lat"]-$p2["lat"], 2) + pow($p1["lon"]-$p2["lon"], 2));
  return $dist;
}

/**
 * Get the closest to $p from the array $arr
 */
function getCloserPoint_Array($p, $arr, $checkid=false){
  $point=FALSE;
  $dist=1000000;
  foreach ($arr as $e){
    if ($checkid && $e->id == $p->id) continue;
    
    $tmp_dist = getDistance($p, $e);
    
    if ($tmp_dist < $dist){
      $point=$e;    
      $dist=$tmp_dist;
    }
  }
  
  return $point;
}

function get2ndCloserPoint_Array($p, $arr, $checkid=false){
  $pr1=FALSE;
  $pr2=FALSE;
  $dist1=1000000;
  $dist2=100000;
  foreach ($arr as $a){
    if ($checkid && $a->id == $p->id) continue;
    
    $tmp_dist = getDistance($p, $a);
     
    if ($tmp_dist < $dist1){
      $pr2=$pr1;
      $dist2=$dist1;
      $pr1=$a;
      $dist1=$tmp_dist;
    }
  }
  
  return $pr2;
}

#
# Find the bounding box of a polygon:
# x1,y1\n
# x2,y2\n
# ...
#
function getBoundingBox($poly){
  $box = array();
  // min
  $box["min"]=array();
  // max
  $box["max"]=array();
  
  // Init
  $box["min"]["y"] = $box["min"]["x"] = 1000000;
  $box["max"]["y"] = $box["max"]["x"] = -1000000;
  
  // Parse
  $parts = explode("\n", $poly);
  // for each row!
  for ($row=0; $row<count($parts)-1; $row++){ 
    // get x,y
    $p = explode(",",$parts[$row]);
    $p_x=$p[0]/1;
    $p_y=$p[1]/1;
    
    if ($p_x<$box["min"]["x"]) $box["min"]["x"] = $p_x;
    if ($p_x>$box["max"]["x"]) $box["max"]["x"] = $p_x;
    
    if ($p_y<$box["min"]["y"]) $box["min"]["y"] = $p_y;
    if ($p_y>$box["max"]["y"]) $box["max"]["y"] = $p_y;
  }
  
  return $box;
  
}

function getArea($poly){
  $box=getBoundingBox($poly);
  return ($box["max"]["x"]-$box["min"]["x"])*($box["max"]["y"]-$box["min"]["y"]);
}


/**
 * Returns true if the point is in the poly_points area. The
 * poly_points is an array of comma separated string values "x,y"!
 */
function insidePoints($point, $poly_points){
  // Split Points
  $polySides = count($poly_points);
  $x = $point->x;
  $y = $point->y;
  
  $empty_count = 0;

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

  // Way 2
  for ($i=0,  $j=$polySides-1; $i<$polySides; $j=$i++){
    if (
	((($polyY[$i] <= $y) && ($y < $polyY[$j])) || (($polyY[$j] <= $y) && ($y < $polyY[$i]))) &&
	($x < ($polyX[$j] - $polyX[$i]) * ($y - $polyY[$i]) / ($polyY[$j] - $polyY[$i]) + $polyX[$i])
	)
	  $result = !$result;
  }
  
  
  return $result;
}

/**
 * Function to return bearing of two points in degrees - 
 * used for determining angle of door triangle...
 * 
 * http://php.net/manual/en/function.atan2.php
 * 
 * angle is with ref to +90 --->East
 */
function bearing($p1, $p2){
  $x_norm = (float)$p1->lat - $p2->lat;
  //take in to account y axis increase going down (ie 0,0 in top left corner)
  $y_norm = (float)$p2->lon - $p1->lon;

  // need to make average points centre and to left = -ve and 
  // below = -ve - hence reason for difference order (eg x= -ve,0 = 270deg)

  if($x_norm==0 && $y_norm==0){ return 0; } // ...or return 360
  return ($x_norm < 0)
  ? rad2deg(atan2($x_norm,$y_norm))+360      // TRANSPOSED !! y,x params
  : rad2deg(atan2($x_norm,$y_norm));
}

function bearing_px($p1, $p2){
  $x_norm = (float)$p1["x"] - $p2["x"];
  //take in to account y axis increase going down (ie 0,0 in top left corner)
  $y_norm = (float)$p2["y"] - $p1["y"];

  // need to make average points centre and to left = -ve and 
  // below = -ve - hence reason for difference order (eg x= -ve,0 = 270deg)

  if($x_norm==0 && $y_norm==0){ return 0; } // ...or return 360
  return ($x_norm < 0)
  ? rad2deg(atan2($x_norm,$y_norm))+360      // TRANSPOSED !! y,x params
  : rad2deg(atan2($x_norm,$y_norm));
}

/**
 * Function to return the bearing given the normalized difference 
 * between two points (on x,y-axis)
 */
function rotation($x,$y)
{
    if($x==0 && $y==0){ return 0; } // ...or return 360
    return ($x < 0)
    ? rad2deg(atan2($x,$y))+360      // TRANSPOSED !! y,x params
    : rad2deg(atan2($x,$y)); 
}


/**
 * Get the closest upper power of 2 from v
 */
function upper_power_of_two($v)
{
    $v--;
    $v |= $v >> 1;
    $v |= $v >> 2;
    $v |= $v >> 4;
    $v |= $v >> 8;
    $v |= $v >> 16;
    $v++;
    return $v;

}

function lineFit($pool){
  $sx = 0;
  $sy = 0;
  $sxy = 0;
  $sx2 = 0;
  
  // Count/T0tal
  $n = count($pool);
  
  foreach ($pool as $v)
  {
      $x=$v->lat;
      $y=$v->lon;
      $sx += $x;
      $sy += $y;
      $sxy += $x * $y;
      $sx2 += $x * $x;
  }
  $alpha = ($n*$sxy - $sx*$sy) / ($n*$sx2 - $sx*$sx);
  $beta = $sy/$n - $sx*$alpha/$n;

  $res = array();
  $res["a"] = $alpha;
  $res["b"] = $beta;
  return $res;
}

function getLineFromPoints($p1, $p2){
  $res = array();
  if ( ($p2->lat - $p1->lat) == 0) $res["a"]=0;
  else $res["a"] = ($p2->lon - $p1->lon) / ($p2->lat - $p1->lat);
  // y -y1 = m(x - x1) for x=0
  $res["b"] =  $p2->lon - $res["a"]*$p2->lat;
  
  return $res;
}

function projectionOnLine($v, $a, $b){
  $res = array();
  
  // Rotate 90 deg (PI/2 rads)
//   $a2 = tan (atan($a)+M_PI/2);
  if ($a == 0) $a2=1;
  else $a2=-1.0/$a;
  // Find b = y - ax
  $b2 = $v->lon - $v->lat*$a2;

  $res["x"] = ($b - $b2)/ ($a2 - $a);
  $res["y"] = $a2*$res["x"] + $b2;
  
  return $res;
}

/**
 * Find the intersection point of the lines ab and cd.
 * a,b,c, and d are VERTICES(lat,lon) of edge points
 */
function findIntersectPoint($a, $b, $c,$d){ 
  $vertical_ab = $vertical_cd =1;
  $M_ab = $M_cd = -1;

  if (($a->lat-$b->lat) != 0) {
	  $M_ab = ($a->lon -$b->lon)/($a->lat-$b->lat);
	  $C_ab = ($a->lon) -($M_ab*$a->lat);
	  $vertical_ab = 0;
  }

  if (($c->lat-$d->lat) != 0) {
	  $M_cd = ($c->lon -$d->lon)/($c->lat-$d->lat);
	  $C_cd = $c->lon - ($M_cd*$c->lat);
	  $vertical_cd = 0;
  }

  //check for parallel lines - these will never intersect!
  if ($M_ab == $M_cd) {
	  return null;
  }
  if (($vertical_ab != 1) && ($vertical_cd != 1)){
	  $I_x = ($C_cd-$C_ab)/($M_ab-$M_cd);
	  $I_y = ($M_cd*$I_x) + $C_cd;
  }
  else if ($vertical_ab == 1){
	  $I_x = $a->lat;
	  $I_y = ($M_cd*$I_x) + $C_cd;
  }
  else if ($vertical_cd == 1){
	  $I_x = $c->lat;
	  $I_y = ($M_ab*$I_x) + $C_ab;
  }

  return array('lat'=>$I_x,'lon'=>$I_y);
}

/**
 * Return TRUE if the point belongs on the line ab
 * Point is ARRAY ["lat"] ["lon"]
 * 
 * NOTE: Setting the DOUBLE_ERROR to 1 (large) results in
 * 2 or more intersection points between 2 lines!
 */
function isPointOnLine($point, $a, $b){
  global $EDGE_INTER_THRES;
  $d1 = getDistanceOA( $a, $point);
  $d2 = getDistanceOA( $b, $point);
  $d = getDistance($a, $b);
  
  return (($d1 + $d2) < ($d +$EDGE_INTER_THRES));
}





?>