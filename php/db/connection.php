<?php

class connection {
   
   var $host;               // mySQL host to connect to
   var $user;               // mySQL user name
   var $pw;                 // mySQL password
   var $db;                 // mySQL database to select
   var $config;	            // database config file

   var $db_link;            // current/last database link identifier
   var $connected;
   
   function __construct($conf_file="") {
   
      // class constructor.  Initializations here.
      $this->host = '';
      $this->user = '';
      $this->pw = '';
      $this->db = '';
      $this->config=$conf_file;
      
      if ($conf_file!="")
	$this->parseConfig();
 
   }
   
   public function parseConfig(){
    $fh = fopen($this->config, 'r') or die("Could not open configuration file ");
    $theData = fread($fh, filesize($this->config));
    fclose($fh);
    
    $res=explode("\n", $theData);
    if (count($res) < 3) die("Wrong Format for configuration file ");
    $this->host=$res[0];
    $this->db=$res[1];
    $this->user=$res[2];
    $this->pw=$res[3];
   }
 
    // Opens a connection to MySQL and selects the database.  If any of the
    // function's parameter's are set, we want to update the class variables.  
    // If they are NOT set, then we're giong to use the currently existing
    // class variables.
    // Returns true if successful, false if there is failure.  
   public function connect($host='', $user='', $pw='', $db='', $persistant=false) {

      
      if (!empty($host)) $this->host = $host; 
      if (!empty($user)) $this->user = $user; 
      if (!empty($pw)) $this->pw = $pw; 

 
      // Establish the connection.
      if ($persistant) $this->host = "p:".$this->host;
      
      $this->db_link = mysqli_connect($this->host, $this->user, $this->pw);
      $this->db_link->set_charset("utf8");  
      // Clean sens. data : No Used for routing
//       $this->pw="";
//       $this->user="";
 
      // Check for an error establishing a connection
      if (!$this->db_link) {
	 $this->last_error = $this->db_link->error;
    
	 $this->connected = false;
         return false;
      } 
  
      // Select the database
      if (!$this->select_db($db)) {
	$this->connected = false;
	return false;
      }

      $this->connected = true;
      return $this->db_link;  // success
      
   }
   
    // Selects the database for use.  If the function's $db parameter is 
    // passed to the function then the class variable will be updated.  
   public function select_db($db='') {
 
 
      if (!empty($db)) $this->db = $db; 
      
      if (!$this->db_link->select_db($this->db)) {
         $this->last_error = $this->db_link->error;
         return false;
      }
 
      return true;
   }

  function checkLink(){
    if (!$this->isConnected()){
	    $this->connect();
	    $this->select_db($this->database);
    }
  }
  
  function connection(){
    $this->connected = false;
  }
  
  
  function isConnected() {
    return $this->connected; 
  }
  
  function close() {
    $this->db_link->close();
    $this->connected = false;
  }
  
  function free($result){
    $result->free();
  }
  
  function send_query($query){
      $result = $this->db_link->query($query);

      if ($result === FALSE){
	$this->last_error = $this->db_link->error;
      }
      return $result;
  }
  
  function lastId(){
    return $this->db_link->insert_id;
  }
}
?>
