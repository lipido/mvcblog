<?php
// Simple REST router

require_once(dirname(__FILE__)."/URIDispatcher.php");

// dinamically include Rest files (*Rest.php) in this directory
$files_in_script_dir = scandir(__DIR__);
foreach($files_in_script_dir as $filename) {
  // if filename ends with *Rest.php
  if (preg_match('/.*REST\\.PHP/', strtoupper($filename))) {
    include_once(__DIR__."/".$filename);
  }
}

try{

//  error_reporting(E_ERROR);

  $dispatched = URIDispatcher::getInstance()->dispatchRequest();

  if (!$dispatched) {
    header($_SERVER['SERVER_PROTOCOL'].' 400 Bad request');
    die("no dispatcher found for this request");
  }

} catch(Exception $ex) {
  header($_SERVER['SERVER_PROTOCOL'].' 500 Internal server error');
  die($ex->getMessage());
}
// debug
//print_r($_SERVER);
//print_r($_GET);
