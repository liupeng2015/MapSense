<?php
class Point{
  public $x;
  public $y;
  
  function __construct($x=-1,$y=-1){
    $this->x = $x;
    $this->y = $y;
  }
  
  function __toString(){
    return "(".$this->x.", ".$this->y.")";
  }
  
  function equals($p){
    return (($this->x == $p->x) && ($this->y == $p->y));
  }
  
  function isZero(){
   return (($this->x == 0) && ($this->y == 0));
  }
  
  function w2gString(){
    return "Point;".$this->x.";".$this->y.";";
  }
  
  function svgString(){
    return "<!-- UnImplemented SVG String -->";
  }
  
  function scale($sx, $sy){
    $this->x *= $sx;
    $this->y *= $sy;
  }
  
  function move($dx,$dy){
    $this->x = $this->x+$dx;
    $this->y = $this->y+$dy;
  }
  
  static function powDist($p1, $p2){
    return pow(($p1->x - $p2->x),2)+ pow(($p1->y - $p2->y),2);
  }
  
  static function dist($p1, $p2){
    return sqrt(pow(($p1->x - $p2->x),2)+ pow(($p1->y - $p2->y),2));
  }
  
  
 /**
  * Return the bearing between two points
  */
  static function bearing($p1, $p2){
    $x_norm = (float)$p1->x - $p2->x;
    //take in to account y axis increase going down (ie 0,0 in top left corner)
    $y_norm = (float)$p2->y - $p1->y;
    
    // need to make average points centre and to left = -ve and 
    // below = -ve - hence reason for difference order (eg x= -ve,0 = 270deg)
    
    if($x_norm==0 && $y_norm==0){ return 0; } // ...or return 360
    return ($x_norm < 0)
    ? rad2deg(atan2($x_norm,$y_norm))+360      // TRANSPOSED !! y,x params
    : rad2deg(atan2($x_norm,$y_norm));
  }
}

class Line {

  public $p1;
  public $p2;
  
  function __construct($p1,$p2){
    $this->p1 = $p1;
    $this->p2 = $p2;
  }
  
}

class w2gGuideVertex extends Point{
  public $id;
  
  function __construct($x=-1, $y=-1, $id=0){
    $this->x=$x;
    $this->y=$y;
    $this->id=$id;
  }
  
  // vg;id;x;y
  function fromParts($parts){
    $this->id = $parts[1];
    $this->x = $parts[2];
    $this->y = $parts[3];
  }
  
  static function getFromParts($parts){
    $tmp_v = new w2gGuideVertex();
    $tmp_v->fromParts($parts);
    return $tmp_v;
  }
  
  function w2gString(){
    return "vg;".$this->id.";".$this->x.";".$this->y;
  }
  
}

class w2gVertex extends Point{
  public $id;
  public $ass_t;
  public $ass_id;
  public $link;
  public $mat;
  public $angle;
  public $index;
  
  // GuideLines array - not saved, mem only
  public $guides;
  
  function __construct($x=-1, $y=-1, $ass_t="", $ass_id=null){
    $this->x=$x;
    $this->y=$y;
    $this->ass_t=$ass_t;
    $this->ass_id=$ass_id;
    $this->guides = array();
  }

  // v;id;x;y;ass_t;ass_id;link;mat;
  function fromParts($parts){
    $this->id = $parts[1];
    $this->x = $parts[2];
    $this->y = $parts[3];
    $this->ass_t = $parts[4];
    $this->ass_id = $parts[5];
    $this->angle =  $parts[6];
    $this->link = $parts[7];
    $this->mat = $parts[8];
    if (count($parts)>9) $this->index = str_replace("\n", "", $parts[9]);
    else  $this->index = "";
  }
  
  static function getFromParts($parts){
    $tmp_v = new w2gVertex();
    $tmp_v->fromParts($parts);
    return $tmp_v;
  }
  
  function w2gString(){
    return "v;".$this->id.";".$this->x.";".$this->y.";".$this->ass_t.";".$this->ass_id.";".$this->angle.";".$this->link.";".$this->mat.";".$this->index;
  }
  
}

class w2gAlias extends Point{
  public $text;
  public $lid;
  
  function __construct($x=-1, $y=-1, $text="", $lid=""){
    $this->x=$x;
    $this->y=$y;
    $this->text=$text;
    $this->lid=$lid;
  }
  
  // A;x;y;text;lid;
  function fromParts($parts){
    $this->x = $parts[1];
    $this->y = $parts[2];
    $this->text = $parts[3];
    $this->lid = str_replace("\n", "",$parts[4]);
  }
  
  static function getFromParts($parts){
    $tmp_a = new w2gAlias();
    $tmp_a->fromParts($parts);
    return $tmp_a;
  }
  
  function w2gString(){
    return "A;".$this->x.";".$this->y.";".$this->text.";".$this->lid.";";
  }
  
}

class w2gEdge {

  public $id;
  public $v1;
  public $v2;
  public $stairs;
  public $inc;
  public $length;
  public $type;
  public $points;
  
  function __construct($v1="",$v2=""){
    $this->v1 = $v1;
    $this->v2 = $v2;
  }
    
  // e;1052;1333;394;;;25
  function fromParts($parts){
    $this->id = $parts[1];
    $this->v1 = $parts[2];
    $this->v2 = $parts[3];
    $this->stairs = $parts[4];
    $this->inc = $parts[5];
    $this->length = str_replace("\n", "", $parts[6]);
    $this->type = str_replace("\n", "", $parts[7]);
    if (count($parts)>8) $this->points = str_replace("\n", "", $parts[8]);
    else $this->points="";
  }
  
  static function getFromParts($parts){
    $tmp_e = new w2gEdge();
    $tmp_e->fromParts($parts);
    return $tmp_e;
  }
  
  function w2gString(){
    return "e;$this->id;$this->v1;$this->v2;$this->stairs;$this->inc;$this->length;$this->type;$this->points";
  }
}

class w2gGuideRail {

  public $id;
  public $v1;
  public $v2;
  
  function __construct($v1="",$v2=""){
    $this->v1 = $v1;
    $this->v2 = $v2;
  }
    
  // e;1052;1333;394;;;25
  function fromParts($parts){
    $this->id = $parts[1];
    $this->v1 = $parts[2];
    $this->v2 = str_replace("\n", "", $parts[3]);
  }
  
  static function getFromParts($parts){
    $tmp_e = new w2gGuideRail();
    $tmp_e->fromParts($parts);
    return $tmp_e;
  }
  
  function w2gString(){
    return "GR;$this->id;$this->v1;$this->v2";
  }
}


class w2gLayerPoint extends Point{
  public $id;
  public $type;
  
  // LP;id;x;y;type
  function fromParts($parts){
    $this->id = $parts[1];
    $this->x = $parts[2];
    $this->y = $parts[3];
    $this->type = str_replace("\n", "", $parts[4]);
  }
  
  static function getFromParts($parts){
    $tmp_lp = new w2gLayerPoint();
    $tmp_lp->fromParts($parts);
    return $tmp_lp;
  }
  
  function w2gString(){
    return "LP;$this->id;$this->x;$this->y;$this->type;";
  }
}

// Same as LayerPoint...
class w2gCID extends w2gLayerPoint{
}

class w2gText extends Point{
  public $id;
  public $mat;
  public $text;
  public $fontsize;
  public $fontfamily;
  public $fill;
  public $loc;
  
  //t;273;0.9941;0.1089;-0.1089;0.9941;148.2383;409.8931;1NW.3.27;2.25;'ArialMT';#FFFFFF;1;0
  function fromParts($parts){
    $this->id = $parts[1];
    $this->mat = $parts[2].";".$parts[3].";".$parts[4].";".$parts[5];
    $this->x = $parts[6];
    $this->y = $parts[7];
    $this->text = $parts[8];
    $this->fontsize = $parts[9];
    $this->fontfamily = $parts[10];
    $this->fill = $parts[11];
    if (count($parts)>12) $this->loc = str_replace("\n", "", $parts[12]);
    else $this->loc = "";
    
    if (count($parts)>13) $this->index = str_replace("\n", "", $parts[13]);
    else $this->index = "";
  }
  
  static function getFromParts($parts){
    $tmp_t = new w2gText();
    $tmp_t->fromParts($parts);
    return $tmp_t;
  }
  
  function w2gString(){
    return "t;".$this->id.";".$this->mat.";".$this->x.";".$this->y.";".$this->text.";".$this->fontsize.";".$this->fontfamily.";".$this->fill.";".$this->loc.";".$this->index;
  }
  
  function svgString(){
    $tmp_mat = str_replace(";", ' ', $this->mat);
    // Always & first
    $tmp_text = str_replace("&", '&amp;', $this->text);
    $tmp_text = str_replace("'", '&apos;', $tmp_text);
    return "<text id = \"T".$this->loc."_".$this->index."\" transform=\"matrix(".$tmp_mat." ".$this->x." ".$this->y.")\" font-family=\"".$this->fontfamily."\" fill = \"".$this->fill."\" font-size=\"".$this->fontsize."\">".$tmp_text."</text>";
  }
}




class w2gLocation extends Point{
  public $id;
  public $name;
  public $points;
  public $fill;
  public $color;
  public $stroke;
  public $layer;
  public $text_indexes;
  
  function __construct($x=-1, $y=-1, $id="", $name=""){
    $this->x=$x;
    $this->y=$y;
    $this->id=$id;
    $this->name=$name;
  }

  //$line_op = "l;".$new_P_ID.";".$location_name.";".$poly_v->x.";".$poly_v->y.";".$P_points.";".$P_fill.";".$P_colour.";".$P_stroke.";".$layer.";".$text_indexes.";\n"; 
  function fromParts($parts){
    $this->id = $parts[1];
    $this->name = $parts[2];
    $this->x = $parts[3];
    $this->y = $parts[4];
    $this->points = $parts[5];
    $this->fill = $parts[6];
    $this->color = $parts[7];
    $this->stroke = $parts[8];
    $this->layer = $parts[9];
    if(count($parts)>10) $this->text_indexes = str_replace("\n", "", $parts[10]);
    else  $this->text_indexes = "";
  }
  
  static function getFromParts($parts){
    $tmp_l = new w2gLocation();
    $tmp_l->fromParts($parts);
    return $tmp_l;
  }
  
  function w2gString(){
    return "l;".$this->id.";".$this->name.";".$this->x.";".$this->y.";".$this->points.";".$this->fill.";".$this->color.";".$this->stroke.";".$this->layer.";".$this->text_indexes;
  }
  
  function svgString(){
    return "<polygon id = \"L".$this->id."\" display=\"inline\" fill=\"".$this->fill."\" stroke=\"".$this->color."\" stroke-width=\"".$this->stroke."\" points=\"".$this->points."\"/>";
  }
  
}















?>
