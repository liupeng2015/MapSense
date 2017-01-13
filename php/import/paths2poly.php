<?php
include_once('simple_html_dom.php');
include_once('importconsts.php');
include_once('SVGPathSeg.php');
include_once('SVGArc.php');
include_once('Tools.php');
//
// EXAMPLE USEAGE:
// php ./paths2poly.php filename=./testp_testb_testf.svg
// 
//


//prtSec("Converting Paths to Polygon for '".$_REQUEST["filename"]."'");
if (!isset($_REQUEST["filename"])){
  prtE("filename parameter is needed");
  return;
}

// Handle File names
$converted_file = $_REQUEST["filename"];
$fbname=basename($converted_file,".svg");

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

//------------------ Here we start -------------------------------- 

/**
 * Quadratic curves TODO:
 */
function getPointQ($A, $B, $C, $t){
  $P = new Point(0,0);
  
  $P->x = pow((1-$t),2) * $A->x + 
	  2 * $t * (1-$t) * $B->x + 
	  pow($t,2) * $C->x;
	  
  $P->y = pow((1-$t),2) * $A->y + 
	  2 * $t * (1-$t) * $B->y + 
	  pow($t,2) * $C->y;
	    
  return $P;
}

/**
 * Cubic curves TODO:
 */
function getPointC($A, $B, $C, $D, $t){
  $P = new Point(0,0);
  
  $P->x = pow((1 - $t), 3) * $A->x + 
	  3 * $t * pow((1 -$t), 2) * $B->x + 
	  3 * (1-$t) * pow($t, 2)* $C->x + 
	  pow ($t, 3)* $D->x;
	  
  $P->y = pow((1 - $t), 3) * $A->y + 
	  3 * $t * pow((1 -$t), 2) * $B->y + 
	  3 * (1-$t) * pow($t, 2)* $C->y + 
	  pow ($t, 3)* $D->y;
	  
  //prt($P->x.",".$P->y);
  //P.z = pow((1 - t), 3) * A.z + 3 * t * pow((1 -t), 2) * B.z + 3 * (1-t) * pow(t, 2)* C.z + pow (t, 3)* D.z;
  
  return $P;
}

$DP=array();
function _getCircleSeg($cp, $type="1"){
  global $doc;
  $p = "<circle cx='".$cp->x."' cy='".$cp->y."' r='0.6' stroke-width='10' />";
  $p .= "<text x='".($cp->x+2) ."' y='".($cp->y+2) ."' font-size='2' >$type</text>";
  return $p;
}


/**
 * Get the reflection of point rel. to $relative point
 */
function getReflection($point, $relative){
  // 2*X3 - X2 : X3 = rel. point, x2 = point
  return new Point(
    2*$relative->x - $point->x,
    2*$relative->y - $point->y
  );
}

function getPointsFromSegments($segs, $step=0.25){
  global $DP, $DEBUG, $NUM_SAMPLES;
  // Current point
  $cp = new Point(0,0);
  $pre = new Point(0,0);
  $points=array();
  
  $prev_ctl_point=new Point(0,0);
  
  foreach ($segs as $s){
    
    switch($s->type){
    case 'PATHSEG_CURVETO_CUBIC_REL':
      
      for ($cmdoffset=0; $cmdoffset<(count($s->x)); $cmdoffset+=3){

	
	$A = $cp;
	$B = new Point($cp->x+$s->x[$cmdoffset+0],$cp->y+$s->y[$cmdoffset+0]);
	$C = new Point($cp->x+$s->x[$cmdoffset+1],$cp->y+$s->y[$cmdoffset+1]);
	$D = new Point($cp->x+$s->x[$cmdoffset+2],$cp->y+$s->y[$cmdoffset+2]);

	
	$tp = new Point(0,0);
	for ($i=0; $i<1; $i+=$step){
	  // temp point
	  $tp = getPointC($A,$B,$C,$D,$i);
	  if ($tp!=$pre){
	    $points[] = clone $tp;
	  }
	  $pre=$tp;
    
	}
	
	$cp=$D;
	$prev_ctl_point=$C;
      }
      
      break;
    case 'PATHSEG_CURVETO_CUBIC_ABS':
      for ($cmdoffset=0; $cmdoffset<(count($s->x)); $cmdoffset+=3){
	$A = $cp; // Here...
	$B = new Point($s->x[$cmdoffset+0],$s->y[$cmdoffset+0]); // Control Point 1
	$C = new Point($s->x[$cmdoffset+1],$s->y[$cmdoffset+1]); // Control Point 2
	$D = new Point($s->x[$cmdoffset+2],$s->y[$cmdoffset+2]); // Curve end
	//prt("---------");
	$tp = new Point(0,0);
	for ($i=0; $i<1; $i+=$step){
	  // temp point
	  $tp = getPointC($A,$B,$C,$D,$i);
	  $points[] = clone $tp;
	
	  $pre=$tp;
	}
	$cp=$D;
	$prev_ctl_point=$C;
      }

      break;
    case 'PATHSEG_CURVETO_CUBIC_SMOOTH_ABS':
      // TODO: Add Forloop
      $A = $cp; // Here...
      
      // This is the reflection of the previous ctlpoint 2
      // relative to the current point...
      if ($prev_ctl_point->isZero()) $B=$cp;         // Control Point 1 eq. current
      else $B = getReflection($prev_ctl_point, $cp); // Control Point 1
      $C = new Point($s->x[0],$s->y[0]); // Control Point 2
      $D = new Point($s->x[1],$s->y[1]); // Curve end
      //prt("---------");
      $tp = new Point(0,0);
      for ($i=0; $i<1; $i+=$step){
	// temp point
	$tp = getPointC($A,$B,$C,$D,$i);
	$points[] = clone $tp;
      
	$pre=$tp;
      }
      $cp=$D;
      $prev_ctl_point=$C;

      break;
      
    case 'PATHSEG_CURVETO_CUBIC_SMOOTH_REL':
      // TODO: Add Forloop
      $A = $cp;
      // This is the reflection of the previous ctlpoint 2
      // relative to the current point...
      if ($prev_ctl_point->isZero()) $B=$cp;         // Control Point 1 eq. current
      else $B = getReflection($prev_ctl_point, $cp); // Control Point 1
      
      $C = new Point($cp->x+$s->x[0],$cp->y+$s->y[0]);
      $D = new Point($cp->x+$s->x[1],$cp->y+$s->y[1]);
      
      $tp = new Point(0,0);
      for ($i=0; $i<1; $i+=$step){
	// temp point
	$tp = getPointC($A,$B,$C,$D,$i);
	//echo "$tp";
	$points[] = clone $tp;
	$pre=$tp;
  
      }
      
      $cp=$D;

      break;
    case 'PATHSEG_CURVETO_QUADRATIC_ABS':
      $A = $cp; // Here...
      $B = new Point($s->x[0],$s->y[0]); // Control Point 1
      $C = new Point($s->x[1],$s->y[1]); // Curve end
      $tp = new Point(0,0);
      for ($i=0; $i<1; $i+=$step){
	// temp point
	$tp = getPointQ($A,$B,$C,$i);
	$points[] = clone $tp;
      
	$pre=$tp;
      }
      $cp=$C;
      $prev_ctl_point=$B;

      break;
    case 'PATHSEG_CURVETO_QUADRATIC_REL':
      $A = $cp; // Here...
      $B = new Point($cp->x+$s->x[0],$cp->x+$s->y[0]); // Control Point 1
      $C = new Point($cp->x+$s->x[1],$cp->x+$s->y[1]); // Curve end
      $tp = new Point(0,0);
      for ($i=0; $i<1; $i+=$step){
	// temp point
	$tp = getPointQ($A,$B,$C,$i);
	$points[] = clone $tp;
      
	$pre=$tp;
      }
      $cp=$C;
      $prev_ctl_point=$B;

      break;
  case 'PATHSEG_CURVETO_QUADRATIC_SMOOTH_ABS':
      $A = $cp; // Here...
      
      if ($prev_ctl_point->isZero()) $B=$cp;         // Control Point 1 eq. current
      else $B = getReflection($prev_ctl_point, $cp); // Control Point 1
      
      $C = new Point($s->x[0],$s->y[0]); // Curve end
      $tp = new Point(0,0);
      for ($i=0; $i<1; $i+=$step){
	// temp point
	$tp = getPointQ($A,$B,$C,$i);
	$points[] = clone $tp;
      
	$pre=$tp;
      }
      $cp=$C;
      $prev_ctl_point=$B;

      break;
   case 'PATHSEG_CURVETO_QUADRATIC_SMOOTH_REL':
      $A = $cp; // Here...
      
      if ($prev_ctl_point->isZero()) $B=$cp;         // Control Point 1 eq. current
      else $B = getReflection($prev_ctl_point, $cp); // Control Point 1
      
      $C = new Point($cp->x+$s->x[0],$cp->x+$s->y[0]); // Curve end
      $tp = new Point(0,0);
      for ($i=0; $i<1; $i+=$step){
	// temp point
	$tp = getPointQ($A,$B,$C,$i);
	$points[] = clone $tp;
      
	$pre=$tp;
      }
      $cp=$C;
      $prev_ctl_point=$B;

      break;
      
    case 'PATHSEG_ARC_REL':
      $arc = new SVGArc();
      $arc->init($cp, $s->x[0], $s->y[0], $s->rotation, 
		   $s->x[1], $s->y[1], // Flags
		   new Point($s->x[2], $s->y[2]) // End-Point
		);
      
      $step=360/$NUM_SAMPLES;
      $tp = new Point(0,0);
	echo "---------\n";
      for ($i=0;  $i<360; $i+=$step){
	// temp point
	$tp = $arc->getPoint($i*0.01);
	$points[] = clone $tp;
      }
      break;
    case 'PATHSEG_MOVETO_ABS':
      $cp->x = $s->x[0];
      $cp->y = $s->y[0];
      for ($i=1; $i<count($s->x); $i++){
	$cp->x = $s->x[$i];
	$cp->y = $s->y[$i];
      }
      break;
    case 'PATHSEG_LINETO_ABS':
      $cp->x = $s->x[0];
      $cp->y = $s->y[0];
      for ($i=1; $i<count($s->x); $i++){
	$points[] = clone $cp;
	$cp->x = $s->x[$i];
	$cp->y = $s->y[$i];
      }
      break;
    case 'PATHSEG_MOVETO_REL':
      $cp->x+=$s->x[0];
      $cp->y+=$s->y[0];
      for ($i=1; $i<count($s->x); $i++){
	$cp->x+=$s->x[$i];
	$cp->y+=$s->y[$i];
      }
      break;
    case 'PATHSEG_LINETO_REL':   
      //echo "---\n";
      $cp->x+=$s->x[0];
      $cp->y+=$s->y[0];
      for ($i=1; $i<count($s->x); $i++){
	$points[] = clone $cp;
	$cp->x+=$s->x[$i];
	$cp->y+=$s->y[$i];
      }
      break;
    case 'PATHSEG_LINETO_VERTICAL_REL':
      $cp->y+=$s->y[0];
      for ($i=1; $i<count($s->y); $i++){
	$points[] = clone $cp;
	$cp->y+=$s->y[$i];
      }
      break;
    case 'PATHSEG_LINETO_HORIZONTAL_REL':
      $cp->x+=$s->x[0]; 
      for ($i=1; $i<count($s->x); $i++){
	$points[] = clone $cp;
	$cp->x+=$s->x[$i];
      }
      break;
    case 'PATHSEG_LINETO_VERTICAL_ABS':
      $cp->y=$s->y[0];
      for ($i=1; $i<count($s->x); $i++){
	$points[] = clone $cp;
	$cp->y=$s->y[$i];
      }
      break;
    case 'PATHSEG_LINETO_HORIZONTAL_ABS':
      $cp->x=$s->x[0];
      for ($i=1; $i<count($s->x); $i++){
	$points[] = clone $cp;
	$cp->x=$s->x[$i];
      }
      break;
    case 'PATHSEG_CLOSEPATH':
      return $points;
      break;
    default:
      prt("Missed Type: ".$s->type);
    };
    
    // Add only unique
  //  if ($pre->x!=$cp->x || $pre->y!=$cp->y){
//     Usual UPM is 0.99 (~1) for overview so 0.8 is slightly less than a metter
    if (abs($pre->x-$cp->x)>0.8 || abs($pre->y-$cp->y)>0.8){
      // Debugin
      if ($DEBUG==true) {
	prt($s->type);
	prt($cp->x.", ".$cp->y);
      }
      
//       echo "Adding: $s->type ".new Point($cp->x,$cp->y)."\n";
      $points[] = clone $cp;
      
      // Urban moved from below the if
      $pre->x=$cp->x;
      $pre->y=$cp->y;

    }else{
//       echo "AlreadyAdded: $s->type ".new Point($cp->x,$cp->y)."\n";
    } 
  }
  

  return $points;
}


function getPolyFromPoints($path, $points){

    $polystr="<polygon fill='".$path->fill."' stroke='".$path->stroke."' style='".$path->style."'";
//     $polystr="<polygon fill='".$path->fill."' stroke='red' stroke-width='2' style='".$path->style."'";
    $polystr.=" points='";
    for ($i=0; $i<count($points); $i++){
      $polystr.=$points[$i]->x.",".$points[$i]->y." ";
    }
    $polystr.="'/>";
    return $polystr;
}

function getPolyLineFromPoints($path, $points){

    $polystr="<polyline fill='".$path->fill."' stroke='".$path->stroke."' style='".$path->style."'";
    $polystr.=" points='";
    for ($i=0; $i<count($points); $i++){
      $polystr.=$points[$i]->x.",".$points[$i]->y." ";
    }
    $polystr.="'/>";
    return $polystr;
}

/**
 * Convert the given path to either Polygon or Polyline
 */
function convertPath($gpath, $polygon=TRUE){
    global $DEBUG, $NUM_SAMPLES;
    $child = $gpath;
   
    // Now we are in the G:locations->G:LWPOLYLINE_*->path!
    $DEBUG && prt("   --->".$gpath->id);
    // remove the path
    $gpath->outertext='';
    
    // Split based on M
    // TODO: Add m: that would need to access the 
    $pstr = $child->d;
    
    $pstr = SVGPathSeg::fixRel_m($child->d);
    
    // previous polyPoints (last one)
    $parts = explode("M",$pstr);
    
   
    foreach ($parts as $p){
      // Avoid p='\n'
      if (trim($p)=="") continue;
      
      $segs = SVGPathSeg::pathSegList("M".$p);

      $polyPoints = getPointsFromSegments($segs,(1.0/$NUM_SAMPLES));
      //foreach( $polyPoints as $p) echo "$p";
      if ($polygon)
	$poly=getPolyFromPoints($child, $polyPoints);
      else 
	$poly=getPolyLineFromPoints($child, $polyPoints);
	
      $gpath->outertext.="\n".$poly;
      
    }
}

prtSubSec("Processing");
// ONLY For Locations G 
foreach ($svg->find("g[id=locations-w2g]") as $gl){
  foreach ($gl->find("path") as $gpath){
    convertPath($gpath);
  }
}

// ONLY For perimeter building outline  
foreach ($svg->find("g[id=perimeter-w2g]") as $gl){
  foreach ($gl->find("path") as $gpath){
    convertPath($gpath);
  }
}

// ONLY For Paths G 
foreach ($svg->find("g[id=paths-w2g]") as $gl){
  foreach ($gl->find("path") as $gpath){
    convertPath($gpath, FALSE);
  }
}



// Save the SVG
prtSubSec("Saving");
$doc->save($PROJECT_DIR."/".$fbname.$P2P_EXT);
prtSubSec("Done");
?>
