<?php
 /**
  * Main router (single entry-point for all requests)
  * of the MVC implementation.
  * 
  * This router will create an instance of the corresponding
  * controller, based on the "controller" parameter and call
  * the corresponding method, based on the "action" parameter.
  * 
  * The rest of GET or POST parameters should be handled by
  * the controller itself.
  * 
  * Parameters:
  * <ul>
  * <li>controller: The controller name (via HTTP GET)
  * <li>action: The name inside the controller (via HTTP GET)
  * </ul>
  * 
  * @return void
  * 
  * @author lipido <lipido@gmail.com>
  */
 function run() {
  // invoke action!
  try {
    $controller = loadController($_GET["controller"]);
    $actionName = $_GET["action"];
    $controller->$actionName(); 
  } catch(Exception $ex) {
    //uniform treatment of exceptions
    die("An exception occured!!!!!".$ex->getMessage());   
  }
 }
 
 /**
  * Load the required controller file and create the controller instance
  * 
  * @param string $controllerName The controller name found in the URL
  * @return Object A Controller instance
  */
 function loadController($controllerName) {  
  $controllerClassName = getControllerClassName($controllerName);
  
  require_once(__DIR__."/controller/".$controllerClassName.".php");  
  return new $controllerClassName();
 }
 
 /**
  * Obtain the class name for a controller name in the URL
  * 
  * For example $controllerName = "users" will return "UsersController"
  * 
  * @param $controllerName The name of the controller found in the URL
  * @return string The controller class name
  */
 function getControllerClassName($controllerName) {
  return strToUpper(substr($controllerName, 0, 1)).substr($controllerName, 1)."Controller";
 }
 
 //run!
 run();
 
 ?>