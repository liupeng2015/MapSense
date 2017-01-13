<?php
/**
 * Try to keep all the math tools required from the import routines here. If the 
 * functions are generic enought (ie they do not work on Point class) move them
 * to libmath.php so they can be used from any other admin interface also.
 *    -- Urb@n
 */

include_once("Tools.php");




/**
 * Calculate centre of gravity of polynomial. Returns a Point.
 */
function polyGravity($points){
  $min_x = 100000;
  $min_y = 100000;
  $max_x = 0;
  $max_y = 0;
	  
  foreach ($points as $p){
    //separate into individual x and y value
    $poly_x_y = explode(',', $p);
    if (count($poly_x_y)<2){
      echo "Wrong Point format!!!: '$p'\n".count($points);
      debug_print_backtrace();
    }
    if ($poly_x_y[0] > $max_x) $max_x = $poly_x_y[0];
    if ($poly_x_y[0] < $min_x) $min_x = $poly_x_y[0];
    if ($poly_x_y[1] > $max_y) $max_y = $poly_x_y[1];
    if ($poly_x_y[1] < $min_y) $min_y = $poly_x_y[1];
  }
  
  return new Point( ($max_x+$min_x)/2, ($max_y+$min_y)/2 );
}

//The above calculates the cog for the bounding box of a room
/**
 * Calculate centre of gravity of polynomial. Returns a Point.
 */
function polyGravity1($points){
  $x_sum = 0;
  $y_sum = 0;


  $num = count($points);
  foreach ($points as $p){
    //separate into individual x and y value
    $poly_x_y = explode(',', $p);
    
	if (count($poly_x_y)<2){
      echo "Wrong Point format!!!: '$p'\n".count($points);
      debug_print_backtrace();
    }
	$x_sum += $poly_x_y[0];
	$y_sum += $poly_x_y[1];

  }
  
  $x_av = $x_sum/$num;
  $y_av = $y_sum/$num;
  return new Point( $x_av, $y_av);
}






/**
 * Find the intersection point of the lines ab and cd.
 * a,b,c, and d are POINTS(x,y)
 */
function find_intersect_point($a, $b, $c,$d){ 
  $vertical_ab = $vertical_cd =1;
  $M_ab = $M_cd = -1;

  if (($a->x-$b->x) != 0) {
	  $M_ab = ($a->y -$b->y)/($a->x-$b->x);
	  $C_ab = ($a->y) -($M_ab*$a->x);
	  $vertical_ab = 0;
  }

  if (($c->x-$d->x) != 0) {
	  $M_cd = ($c->y -$d->y)/($c->x-$d->x);
	  $C_cd = $c->y - ($M_cd*$c->x);
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
	  $I_x = $a->x;
	  $I_y = ($M_cd*$I_x) + $C_cd;
  }
  else if ($vertical_cd == 1){
	  $I_x = $c->x;
	  $I_y = ($M_ab*$I_x) + $C_ab;
  }

  return new Point($I_x,$I_y);
}



function getStringPxSize($str, $font, $size, $pxsize=""){
  global $TOOLsDIR;
  
  $output=array();
  $cmd = "$TOOLsDIR/fsize/fsize '$str' $font $size";
  if ($pxsize!="") $cmd .= " $pxsize";
  exec ( $cmd,  $output );
  
  $res = $output[0];
  if ($res=="") return array("w"=>0, "h"=>0, "f"=>"");
  
  $parts = explode("x", $res);
  return array("w"=>$parts[0], "h"=>$parts[1], "f"=>$output[1]);
}


//function to create a new normalised point at a bearing away from a given coordinate of length $l
function move_point($point, $bearing,$l) {
  if ($bearing >=360) $bearing = $bearing -360;//normalise
  if ($bearing <0) $bearing = $bearing +360;//normalise
  $quadrant = floor(($bearing)/90);
  //echo ($bearing."=quadrant".$quadrant."<br/>");

  //quadrant numbering not standard - reverse of normal
  switch ($quadrant){
    case 0://quadrant 1 (clockwise)
    $PPx = (($point->x) + $l*sin(deg2rad($bearing)));
    $PPy = (($point->y) - $l*cos(deg2rad($bearing)));  //coordinate system is 0,0 at top (normally +ve)
    break;
    case 1: 
    $PPx = (($point->x) + $l*sin(deg2rad($bearing)));
    $PPy = (($point->y) - $l*cos(deg2rad($bearing)));
    break;
    case 2:
    $PPx = (($point->x) + $l*sin(deg2rad($bearing)));
    $PPy = (($point->y) - $l*cos(deg2rad($bearing)));  //coordinate system is 0,0 at top (normally +ve)
    break;
    case 3://quadrant 4
    $PPx = (($point->x) + $l*sin(deg2rad($bearing)));
    $PPy = (($point->y) - $l*cos(deg2rad($bearing)));
    break;
  }
  $new_point = new Point($PPx,$PPy);
  return  $new_point;
};




?>
