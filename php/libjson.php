<?php


class JSONLoader{
  var $____defaults="";
  var $configFile;
  var $content;

  function __construct($conf=""){
    $this->initDefaults();
    
    if ($conf=="") return;
    
    $this->load($conf);
    $this->configFile = $conf;
  }
  
  function initDefaults(){
    if ($this->____defaults!="")
      $this->content = json_decode ($this->____defaults, true);
    else $this->content = array();
    
  }
  
  public function load($conf){
    if (!file_exists($conf)) return;
    
    $str = file_get_contents($conf);
    $styles_tmp = json_decode ($str, true);

    $this->content = array_merge($this->content, $styles_tmp);
  }
  
  public function get($type, $prop){
    if (!isset($this->content[$type])) return FALSE;
    if (!isset($this->content[$type][$prop])) return FALSE;
     
    return $this->content[$type][$prop];
  }
  
  public function getP($path){
    $parts = explode("|",$path);    
    $arr = &$this->content;
    foreach ($parts as $p){
      if (!isset($arr[$p]) ) return FALSE;
      $arr = &$arr[$p];
    }
    
    return $arr;
  }
  
  
  public function setP($path, $value){
    $parts = explode("|",$path);    
    $arr = &$this->content;
    foreach ($parts as $p){
      if (!isset($arr[$p]) ) $arr[$p]=array();
      $arr = &$arr[$p];
    }
    $arr = $value;
    return $arr;
  }
  
  public function set($type, $prop, $value){

    if (!isset($this->content[$type])) return FALSE;
    if (!isset($this->content[$type][$prop])) return FALSE;
    $this->content[$type][$prop] = $value;
    return TRUE;
  }
  
  public function __toString(){
    $str = json_encode($this->content);

    if (!$str || $str===FALSE) return "JSON Code Error : ".json_last_error();
    
    return prettifyJSON($str);
  }
  
  public function save($new_file=""){
    $target = $this->configFile;
    if ($new_file!="") $target = $new_file;
    
    $rc = file_put_contents($target,prettifyJSON(json_encode($this->content)));
    return $rc;
  }
}

class BaseStyles extends JSONLoader {
 var $____default_style='
  {
    "perimeter": {
      "width" : 0.08,
      "fill" : "#FFFFFF",
      "color" : "#FFFFFF"
    },
    "door" : {
      "width" : 0.08,
      "fill" : "#FFFFFF",
      "color" : "#FFFFFF",
      "size" : 0.5,
      "type" : 0
    },
    "entrance" : {
      "fill" : "#FFFFFF",
      "color" : "#FFFFFF",
      "size" : 0.75,
      "type" : 0
    },
    "polygon" : {
      "fill" : "#FFFFFF",
      "color" : "#FFFFFF",
      "width" : 0.05
    }
  }';
  
}

class CatConf extends JSONLoader {
}


?>
