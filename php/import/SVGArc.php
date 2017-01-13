
<?php

class SVGArc { 
  public $x;
  public $y;
  public $width;
  public $height;
  public $start;
  public $extent;
  public $rotation;
  public $rx;
  public $ry;
  public $cx;
  public $cy;
  
  
  public $theta;
  public $delta;
  
  /**
   * P0 : current point
   * rx,ry: orientation/size radii (radius on x and y)
   * angle: rotation
   * flags (true/false) 
   * EP: End Point
   * 
   * Code From: http://svn.apache.org/repos/asf/xmlgraphics/batik/branches/svg11/sources/org/apache/batik/ext/awt/geom/ExtendedGeneralPath.java
   */
//   function init($P0, $rx, $ry, $angle, $largeArcFlag, $sweepFlag, $EP){
//       // Separate x/y s
//       $x0 = $P0->x;
//       $y0 = $P0->y;
//       
//       $x = $EP->x;
//       $y = $EP->y;
//       
//       $this->rotation=$angle;
//       $this->rx=$rx;
//       $this->ry=$ry;
//       //
//       // Elliptical arc implementation based on the SVG specification notes
//       //
// 
//       // Compute the half distance between the current and the final point
//       $dx2 = ($x0 - $x) / 2.0;
//       $dy2 = ($y0 - $y) / 2.0;
//       // Convert angle from degrees to radians
//       $angle = deg2rad($angle % 360.0);
//       $cosAngle = cos($angle);
//       $sinAngle = sin($angle);
// 
//       //
//       // Step 1 : Compute (x1, y1)
//       //
//       $x1 = ($cosAngle * $dx2 + $sinAngle * $dy2);
//       $y1 = (-$sinAngle * $dx2 + $cosAngle * $dy2);
//       // Ensure radii are large enough
//       $rx = abs($rx);
//       $ry = abs($ry);
//       $Prx = $rx * $rx;
//       $Pry = $ry * $ry;
//       $Px1 = $x1 * $x1;
//       $Py1 = $y1 * $y1;
//       // check that radii are large enough
//       $radiiCheck = $Px1/$Prx + $Py1/$Pry;
//       if ($radiiCheck > 1) {
// 	  $rx = sqrt($radiiCheck) * $rx;
// 	  $ry = sqrt($radiiCheck) * $ry;
// 	  $Prx = $rx * $rx;
// 	  $Pry = $ry * $ry;
//       }
// 
//       //
//       // Step 2 : Compute (cx1, cy1)
//       //
//       $sign = ($largeArcFlag == $sweepFlag) ? -1 : 1;
//       $sq = (($Prx*$Pry)-($Prx*$Py1)-($Pry*$Px1)) / (($Prx*$Py1)+($Pry*$Px1));
//       $sq = ($sq < 0) ? 0 : $sq;
//       $coef = ($sign * sqrt($sq));
//       $cx1 = $coef * (($rx * $y1) / $ry);
//       $cy1 = $coef * -(($ry * $x1) / $rx);
// 
//       //
//       // Step 3 : Compute (cx, cy) from (cx1, cy1)
//       //
//       $sx2 = ($x0 + $x) / 2.0;
//       $sy2 = ($y0 + $y) / 2.0;
//       $cx = $sx2 + ($cosAngle * $cx1 - $sinAngle * $cy1);
//       $cy = $sy2 + ($sinAngle * $cx1 + $cosAngle * $cy1);
// 
//       //
//       // Step 4 : Compute the angleStart (angle1) and the angleExtent (dangle)
//       //
//       $ux = ($x1 - $cx1) / $rx;
//       $uy = ($y1 - $cy1) / $ry;
//       $vx = (-$x1 - $cx1) / $rx;
//       $vy = (-$y1 - $cy1) / $ry;
//       
//       // Compute the angle start
//       $n = sqrt(($ux * $ux) + ($uy * $uy));
//       $p = $ux; // (1 * ux) + (0 * uy)
//       $sign = ($uy < 0) ? -1.0 : 1.0;
//       $angleStart = rad2deg($sign * acos($p / $n)); // theta
// 
//       // Compute the angle extent
//       $n = sqrt(($ux * $ux + $uy * $uy) * ($vx * $vx + $vy * $vy));
//       $p = $ux * $vx + $uy * $vy;
//       $sign = ($ux * $vy - $uy * $vx < 0) ? -1.0 : 1.0;
//       $angleExtent = rad2deg($sign * acos($p / $n));  // delta
//       
//       if(!$sweepFlag && $angleExtent > 0) {
// 	  $angleExtent -= 360.0;
//       } else if ($sweepFlag && $angleExtent < 0) {
// 	  $angleExtent += 360.0;
//       }
//       
//       $angleExtent %= 360.0;
//       $angleStart %= 360.0;
// 
//       //
//       // We can now build the resulting Arc2D in $precision
//       //
//       $this->x = $cx - $rx;
//       $this->y = $cy - $ry;
//       $this->width = $rx * 2.0;
//       $this->height = $ry * 2.0;
//       $this->start = -$angleStart;
//       $this->extent = -$angleExtent;
// 
//   }
//   

  /**
   * pos: corner (f) in degrees
   */
//   function getPoint($pos){
//     $angle = deg2rad($this->start + ($this->extent * $pos));
//     echo "ssss = ".($this->start + ($this->extent * $pos));
//     echo "s=".$angle."\n";
// 
//     $cosr = cos(deg2rad($this->rotation));
//     $sinr = sin(deg2rad($this->rotation));
//     
//     $x = $cosr * cos($angle) * $this->rx - $sinr * sin($angle) * $this->ry + $this->x;
//     $y = $sinr * cos($angle) * $this->rx + $cosr * sin($angle) * $this->ry +  $this->y;
//     echo "$x, $y\n";
//     return new Point($x, $y);
//   }
  
  function init($P0, $rx, $ry, $angle, $largeArcFlag, $sweepFlag, $EP){
    // Separate x/y s
    $x0 = $P0->x;
    $y0 = $P0->y;
    
    $x = $EP->x;
    $y = $EP->y;
    
    $this->rotation=$angle;
    $this->rx=$rx;
    $this->ry=$ry;
    
    
    $cosr = cos(deg2rad($angle));
    $sinr = sin(deg2rad($angle));
    $dx = ($x0 - $x) / 2;
    $dy = ($y0 - $y) / 2;
    $x1prim = $cosr * $dx + $sinr * $dy;
    $x1prim_sq = $x1prim * $x1prim;
    $y1prim = -$sinr * $dx + $cosr * $dy;
    $y1prim_sq = $y1prim * $y1prim;

    //rx = self.radius.real
    $rx_sq = $rx * $rx;
    //ry = self.radius.imag        
    $ry_sq = $ry * $ry;

        # Correct out of range radii
    $radius_check = ($x1prim_sq / $rx_sq) + ($y1prim_sq / $ry_sq);
    if ($radius_check > 1){
	$rx *= sqrt($radius_check);
	$ry *= sqrt($radius_check);
	$rx_sq = $rx * $rx;
	$ry_sq = $ry * $ry;
    }

    
    $t1 = $rx_sq * $y1prim_sq;
    $t2 = $ry_sq * $x1prim_sq;
    $c = sqrt((int)($rx_sq * $ry_sq - $t1 - $t2) / ($t1 + $t2));
    if ($c==0) $c=1; // Urban
    if ($largeArcFlag == $sweepFlag){
      $c = -$c;
    }
    
    $cxprim = $c * $rx * $y1prim / $ry;
    $cyprim = -$c * $ry * $x1prim / $rx;

//         self.center = complex((cosr * cxprim - sinr * cyprim) + 
//                               ((self.start.real + self.end.real) / 2),
//                               (sinr * cxprim + cosr * cyprim) + 
//                               ((self.start.imag + self.end.imag) / 2))

    $this->cx = ($cosr * $cxprim - $sinr * $cyprim) + (($x0 + $x) / 2);                  
    $this->cy = ($sinr * $cxprim + $cosr * $cyprim) + (($y0 + $y) / 2);

    $ux = ($x1prim - $cxprim) / $rx;
    $uy = ($y1prim - $cyprim) / $ry;
    $vx = (-$x1prim - $cxprim) / $rx;
    $vy = (-$y1prim - $cyprim) / $ry;
    $n = sqrt($ux * $ux + $uy * $uy);
    $p = $ux;
    $theta = rad2deg(acos($p / $n));
    if ($uy < 0) 
      $theta = -$theta;
      
    $this->theta = $theta % 360;

    $n = sqrt(($ux * $ux + $uy * $uy) * ($vx * $vx + $vy * $vy));
    $p = $ux * $vx + $uy * $vy;
    if ($p == 0)
	$delta = rad2deg(acos(0));
    else
	$delta = rad2deg(acos($p / $n));
	
    if (($ux * $vy - $uy * $vx) < 0)
	$delta = -$delta;
	
    $this->delta = $delta % 360;
    if (!$sweepFlag)
	$this->delta -= 360;
            
   
  }
  
  function getPoint($pos){
    $angle = deg2rad($this->theta + ($this->delta * $pos));

    $cosr = cos(deg2rad($this->rotation));
    $sinr = sin(deg2rad($this->rotation));
    
    echo "$angle\n";
    $x = $cosr * cos($angle) * $this->rx - $sinr * sin($angle) * $this->ry + $this->cx;
    $y = $sinr * cos($angle) * $this->rx + $cosr * sin($angle) * $this->ry +  $this->cy;
    echo ">>>>>>>>>". $this->cy ;
    echo "$x, $y";
    return new Point($x, $y);
  }

}

?>
