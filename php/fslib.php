<?php
# Assuming project_settings is loaded..
include_once ("simple_html_dom.php"); # use for video fix...
include_once ("global_settings.php");

/**
 * File system and other IO tools
 */

$PROJECT_DATA_DIR="/projects";


function makeMIPath($prefix, $suffix){
  global $config;
    
  return $prefix."projects/$config/info/$suffix/";
}

/// True/false if there are info for location 
function hasMoreInfo($locname, $prefix="", $suffix=""){

  if ($locname=="") return FALSE;
  $lc_loc=strtolower($locname);

  return file_exists (makeMIPath($prefix, $suffix).$lc_loc);
}

/// Get the location moreInfo file path
function getMoreInfoFile($locname, $prefix="", $suffix=""){
  
  if (!hasMoreInfo($locname, $prefix, $suffix)) return "";
  $lc_loc=strtolower($locname);
  
  return makeMIPath($prefix, $suffix).$lc_loc;
}



function do_replaces($string){
  $string=str_replace("\n", "", $string);
  $string=str_replace("\r", "", $string);
  $string=str_replace("\"", "\\\"", $string);
//   $string=str_replace("'", "\\'", $string);
  return $string;
}

/// Replace image sources...
function correctImgSrc($string, $prefix="", $suffix=""){
  global $config;

  $string=str_replace("src=\"images", "src=\"".makeMIPath($prefix, $suffix)."images", $string);
  $string=str_replace("url=\"images", "url=\"".makeMIPath($prefix, $suffix)."images", $string);
  # Change video-flash sources
  $string=str_replace("lib/js/tinymce/plugins/media/moxieplayer.swf", 
		      "${prefix}lib/js/tinymce/plugins/media/moxieplayer.swf", $string);
		      

  return $string;
}


/**
 * Return the contents of the more info file RAW and ONLY (no headers)
 */
function getMoreInfo($locname, $prefix="",$suffix=""){
  global $config;
  $lc_loc=strtolower($locname);
  
  $info="No more Info: Can't open file";
  
  //$fh  = fopen(makeMIPath($prefix,$suffix).$lc_loc, 'r');
  //$info= fread($fh,filesize(makeMIPath($prefix,$suffix).$lc_loc));
  //fclose($fh);
  $info= readMoreInfo(makeMIPath($prefix,$suffix).$lc_loc);
  return do_replaces(correctImgSrc($info,$prefix,$suffix));
}



/**
 * Check if a tour folder exists... if it is do nothing
 * but if not: create it, copy the default common.css and
 * create the images/media folder
 * 
 * Returns TRUE if the folder just created...
 */
function checkNCreateTourFolder($tourid, $prefix=""){
  global $config;
  
  $dirpath=$prefix."projects/$config/info/tours/$tourid/";

  if (file_exists ($dirpath) ) return 0;
  
  $rc=mkdir($dirpath);
  if ($rc===FALSE) return FALSE;
  $rc=mkdir("$dirpath/images");
  if ($rc===FALSE) return FALSE;
  
  copy($dirpath."../../common.css", $dirpath."/common.css");
  
  
  return TRUE;
}

function getMoreInfoWithHead($locname, $prefix="", $suffix=""){
  global $config;
  
  $lc_loc=strtolower($locname);
  
  $info="No more Info: Can't open file";

  $fname=makeMIPath($prefix, $suffix).$lc_loc;
  

  //$fh = fopen($fname, 'r');
  
  //$info=fread($fh,filesize($fname));

  //fclose($fh);
  $info= readMoreInfo($fname);
  // Check for a single link!
  $chars = substr($info,12,4);

  if ($chars=="http"){
    $link = preg_replace("/.*href=\"([^\"]*)\".*/","$1",$info);
    return $link;
  }
  
  $info=do_replaces(correctImgSrc($info,$prefix,$suffix));
  // Urban: Keep only one project wide common.css rather than one in each sub folder
  $info2  = "<head><link rel=\\\"stylesheet\\\" type=\\\"text/css\\\" href=\\\"{$prefix}projects/$config/info/common.css\\\" />";
  if ($suffix!="" && file_exists("{$prefix}projects/$config/info/$suffix/common.css")){
    $info2 .= "<link rel=\\\"stylesheet\\\" type=\\\"text/css\\\" href=\\\"{$prefix}projects/$config/info/$suffix/common.css\\\" />";
  }
  $info2 .= "<meta http-equiv=\\\"Content-Type\\\" content=\\\"text/html; charset=utf-8\\\"></head><body>".$info."</body>";
  
  
  // Always return encoded content so it does not 
  // interfere with page's html
  $info=htmlentities($info2,ENT_QUOTES,"UTF-8");
  
  // Add
  $info = "<html>".$info."</html>";

   return $info;   
}

function getMoreInfoDiv($locname, $prefix="", $suffix=""){
  global $config;
  $lc_loc=strtolower($locname);
  
  $info="No more Info: Can't open file";

  $fname=makeMIPath($prefix, $suffix).$lc_loc;

  //$fh = fopen($fname, 'r');
  //$info=fread($fh,filesize($fname));

  //fclose($fh);
  $info= readMoreInfo($fname);
  $info=do_replaces(correctImgSrc($info,$prefix,$suffix));
  // Urban: Keep only one project wide common.css rather than one in each sub folder
  $info2  = "<link rel=\\\"stylesheet\\\" type=\\\"text/css\\\" href=\\\"{$prefix}projects/$config/info/common.css\\\" />";
  if (file_exists("{$prefix}projects/$config/info/$suffix/common.css")){
    $info2 .= "<link rel=\\\"stylesheet\\\" type=\\\"text/css\\\" href=\\\"{$prefix}projects/$config/info/common.css\\\" />";
  }
  $info2 .= $info;
	    
//   $info="<head><link rel=\\\"stylesheet\\\" type=\\\"text/css\\\" href=\\\"{$prefix}projects/$config/info/$suffix/common.css\\\" />".
// 	    "<meta http-equiv=\\\"Content-Type\\\" content=\\\"text/html; charset=utf-8\\\"></head><body>".$info."</body>";
	    
  $info=htmlentities($info2,ENT_QUOTES,"UTF-8");

   return $info;   
}


function projectExist($string){
  global $SRV;
  if (file_exists ($SRV['BASE_CONF'].$string."_db.conf"))
	return TRUE;
  
  return FALSE;
}

/**
 * Check if the info folder exists... if it is do nothing
 * but if not: create it, create the images/media folder
 * 
 * Returns TRUE if the folder just created...
 * Returns FALSE on error...
 * Returns 0 if the folder was there
 */
function checkNCreateInfoFolder($prefix=""){
  global $config;
  
  $dirpath=$prefix."projects/$config/info/";
  if (file_exists ($dirpath) ) return 0;
  
  $rc=mkdir($dirpath);
  if ($rc===FALSE) return FALSE;
  $rc=mkdir("$dirpath/images");
  if ($rc===FALSE) return FALSE;
  
  return TRUE;
}
/**
 * Save a location's more information...
 */
function saveMoreInfo($loc, $info, $prefix=""){
  global $config;
  
  $fpath=$prefix."projects/$config/info/".strtolower($loc);
  
  // replace any image paths
  $info=preg_replace("/(src=\").*(images\/.*)/", "\\1\\2",$info);
  
  // Write file
  $rc=file_put_contents($fpath, $info);
  return $rc;
}

/**
 * Delete a location's more information...
 */
function deleteMoreInfo($loc, $prefix=""){
  global $config;
  
  $fpath=$prefix."projects/$config/info/".strtolower($loc);
  
  // Delete file
  $rc=unlink($fpath);
  
  return $rc;
}

/**
 * Delete a POI's more information...
 */
function deleteMoreInfoPOI($poi, $prefix=""){
  global $config;
  
  $fpath=$prefix."projects/$config/info/tours/".$poi->get_tid()."/".$poi->id;
  
  // Delete file
  $rc=unlink($fpath);
  
  return $rc;
}

/**
 * Save a Point's of Interest more information...
 */
function saveMoreInfoPOI($poi, $info, $prefix=""){
  global $config;
  
  $fpath=$prefix."projects/$config/info/tours/".$poi->get_tid()."/".$poi->id;
  
  $info = str_replace("'","\'",$info);
  // replace any image paths
  $info=preg_replace("/(src=\").*(images\/.*\")/", "\\1\\2",$info);
  $info=preg_replace("/(url=\").*(images\/.*\")/", "\\1\\2",$info);
  
  // Write file
  $rc=file_put_contents($fpath, $info);
  return $rc;
}

function fixTheFuckinVideo($more_info, $prefix=""){
  return $more_info;
//   $html = str_get_html("<html><body>$more_info</body></html>");
//   
//   foreach ($html->find("video") as $video){
//     $src = $video->find("source");
//     $src = $src[0];
//     $src=$src->getAttribute("src");
// 
//     $w=$video->getAttribute("width");
//     $h=$video->getAttribute("height");
//     
//     $video->setAttribute("controls","controls");
//     //$video->setAttribute("type","video/mp4; codecs=\"avc1.42E01E, mp4a.40.2\"");
//     
//     $video->first_child()->outertext='';
//     $video->innertext="
//     <object width=\"$w\" height=\"$h\" data=\"${prefix}lib/js/tinymce/plugins/media/moxieplayer.swf\" type=\"application/x-shockwave-flash\">
//       <param name=\"src\" value=\"${prefix}lib/js/tinymce/plugins/media/moxieplayer.swf\" />
//       <param name=\"flashvars\" value=\"url=$src\" />
//       <param name=\"allowfullscreen\" value=\"true\" /><param name=\"allowscriptaccess\" value=\"true\" />
//     </object>
//     ";
//     
//   }
//   $body = $html->find("body");
//   return $body[0]->innertext;
}



function getAllProjectsSel($prefix="."){
  global $PROJECT_DATA_DIR;
  
  
  //get all files in specified directory
  $files = glob("$prefix$PROJECT_DATA_DIR/*");

  //print each file name
  $opts="";
  
  foreach($files as $file)
  {
    //check to see if the file is a folder/directory
    if(is_dir($file))
    {
      $bn = basename($file);
      $opts.="<option value='$bn'>$bn</option>";
    }
  }
  
  return $opts;
 }

/**
 * Load default settings into an array and merge them with the 
 * project settings
 */
function getMergedCS($sec=TRUE){
  global $config,$FS_PRE;
  
  $res = array();
  
  // default settings
  $path = "$FS_PRE/lib/js/cs.json.js";
  if (!file_exists($path)) return $res;
  
  $str = file_get_contents($path);
  $res = json_decode ($str, true);
  if (!$res || $res===FALSE) {
    echo "<!--JSON Code Error : ".json_last_error()."-->";
    return;
  }
  
  // project's settings
  $path = "$FS_PRE/projects/$config/php/cs.json.js";
  if (!file_exists($path)) return $res;
  $str = file_get_contents($path);
  $tmp = json_decode ($str, true);
  $res = array_replace_recursive($res, $tmp);
  
  // Security filter
  if ($sec){
    if (isset($res["feedback"]["mailList"])) unset($res["feedback"]["mailList"]);
  }
  
  return $res;
}

function readMoreInfo($path){
	$fh  = fopen($path, 'r');
	$info= fread($fh,filesize($path));
	fclose($fh);
	return $info;
}
?>