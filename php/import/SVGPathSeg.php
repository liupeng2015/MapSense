<?php

class SVGPathSeg {
  // Constants
  // Path Segment Types
  const PATHSEG_UNKNOWN = 0;
  const PATHSEG_CLOSEPATH = 1;
  const PATHSEG_MOVETO_ABS = 2;
  const PATHSEG_MOVETO_REL = 3;
  const PATHSEG_LINETO_ABS = 4;
  const PATHSEG_LINETO_REL = 5;
  const PATHSEG_CURVETO_CUBIC_ABS = 6;
  const PATHSEG_CURVETO_CUBIC_REL = 7;
  const PATHSEG_CURVETO_QUADRATIC_ABS = 8;
  const PATHSEG_CURVETO_QUADRATIC_REL = 9;
  const PATHSEG_ARC_ABS = 10;
  const PATHSEG_ARC_REL = 11;
  const PATHSEG_LINETO_HORIZONTAL_ABS = 12;
  const PATHSEG_LINETO_HORIZONTAL_REL = 13;
  const PATHSEG_LINETO_VERTICAL_ABS = 14;
  const PATHSEG_LINETO_VERTICAL_REL = 15;
  const PATHSEG_CURVETO_CUBIC_SMOOTH_ABS = 16;
  const PATHSEG_CURVETO_CUBIC_SMOOTH_REL = 17;
  const PATHSEG_CURVETO_QUADRATIC_SMOOTH_ABS = 18;
  const PATHSEG_CURVETO_QUADRATIC_SMOOTH_REL = 19;
  
  // Members
  public $x;
  public $y;
  public $type;
  public $rotation;
  
  function __construct($type=0,$x=0,$y=0){
    $this->type = $type;
    $this->x = $x;
    $this->y = $y;
  }
  
  function initFromText($text){
    $this->type = $this->getTypeFromText($text);
    if ($this->type=='PATHSEG_CLOSEPATH') return;
    $this->getCoordsFromText($text);
  }
  
  function getTypeFromText($text){
    $t = $text{0};
    
    switch($t){
      case "Z":
      case "z": return 'PATHSEG_CLOSEPATH';
      case "M": return 'PATHSEG_MOVETO_ABS';
      case "m": return 'PATHSEG_MOVETO_REL';
      case "L": return 'PATHSEG_LINETO_ABS';
      case "l": return 'PATHSEG_LINETO_REL';
      case "H": return 'PATHSEG_LINETO_HORIZONTAL_ABS';
      case "h": return 'PATHSEG_LINETO_HORIZONTAL_REL';
      case "V": return 'PATHSEG_LINETO_VERTICAL_ABS';
      case "v": return 'PATHSEG_LINETO_VERTICAL_REL';
      case "C": return 'PATHSEG_CURVETO_CUBIC_ABS';
      case "c": return 'PATHSEG_CURVETO_CUBIC_REL';
      case "Q": return 'PATHSEG_CURVETO_QUADRATIC_ABS';
      case "q": return 'PATHSEG_CURVETO_QUADRATIC_REL';
      case "S": return 'PATHSEG_CURVETO_CUBIC_SMOOTH_ABS';
      case "s": return 'PATHSEG_CURVETO_CUBIC_SMOOTH_REL';
      case "T": return 'PATHSEG_CURVETO_QUADRATIC_SMOOTH_ABS';
      case "t": return 'PATHSEG_CURVETO_QUADRATIC_SMOOTH_REL';
      case "A": return 'PATHSEG_ARC_ABS';
      case "a": return 'PATHSEG_ARC_REL';
      default:  return 'PATHSEG_UNKNOWN';
    }
  }
  
  /**
   * Parse each sub command of a path (ie from letter to letter)
   */ 
  function getCoordsFromText($text){
// echo "Constructing Path: $text\n";
    $str = trim(substr($text, 1));
    
    $p = preg_split("/[, ]+/", $str);
    
    $this->x=array();
    $this->y=array();
    
    if (count($p)==0) return;
    
    $c=0;
    for ($i=0; $i<count($p); $i+=2){
      
      
      
      // Single attribute types
      if ($this->type=='PATHSEG_LINETO_HORIZONTAL_REL' || $this->type=='PATHSEG_LINETO_VERTICAL_REL' ||
	  $this->type=='PATHSEG_LINETO_HORIZONTAL_ABS' || $this->type=='PATHSEG_LINETO_VERTICAL_ABS'){
	   $this->y[$c]= $this->x[$c]=doubleval($p[$i]);
      }
      // Double+ attribute types
      else{
	$this->x[$c]=doubleval($p[$i]);
	$this->y[$c]=doubleval($p[$i+1]);
	
	// Arcs: 3 attrs
	// Read rotation after first x/y (rx/ry)
	if ( $i==0 && ($this->type=='PATHSEG_ARC_REL' || $this->type=='PATHSEG_ARC_ABS')){
	  $this->rotation=doubleval($p[$i+2]);
	  $i++;
	}
      }
      
      $c++;
      
      
    }
    
  }
  
  public static function pathSegList($fulltext) {
  
//  echo "FULL from :$fulltext\n";
    $fulltext=trim($fulltext);
    $list = array();
    
    // Safety check ... return empty list 
    if (trim($fulltext)=="M") return $list;
    
    $fulltext=preg_replace("/[\n\r]/"," ",$fulltext);
    
    // Ensure there is a space before (-) symbols
    $fulltext=preg_replace("/\-/"," -",$fulltext);
    
    // Replace multiple spaces with one
    $fulltext=preg_replace("/\s+/"," ",$fulltext);
    
    // Split based on character
    $parts=preg_split("/([a-zA-Z])/",$fulltext, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

    
    
    $p="";
    for ($i=0; $i<count($parts); $i+=2){
    
      $p.=$parts[$i];
      // Not closing
      if ($p!="z" && $p!="Z"){
	$p.=$parts[$i+1];
      }
      
      //echo "P: ".$p."\n";
      $p=trim($p);
      $ps = new SVGPathSeg();
//       echo "Init from :$p\n";
      $ps->initFromText($p);
//       var_dump($ps);
      array_push($list,$ps);
      $p="";
      
    }
      
    return $list;
  }
  
  
  /**
   * Fix relevant moveto at the begining of a path. The first "m" is treated
   * like "M" but the rest remain "m"... They are tho actually treated as "l"
   */
  public static function fixRel_m($d) {
    if ($d[0]=="m") {
	// Fix first m... arg fucking w3c assholes
	$space_pos = strpos ($d , " ",3);
	preg_match("/[a-zA-Z]/", $d, $matches, PREG_OFFSET_CAPTURE,3);
	$letter_pos = $matches[0][1];
      
	// Multiple relevant moves...
	if ($letter_pos-1>$space_pos){
	  $p1 = substr($d,0,$space_pos);
	  $p2 = substr($d,$space_pos+1);
	  $d="$p1 l$p2";
	}
	$d[0]="M";
      }
      return $d;
   }
  
}
?> 
