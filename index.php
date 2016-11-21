<?php
// file: index.php

/**
* Default controller if any controller is passed in the URL
*/
define("DEFAULT_CONTROLLER", "posts");

/**
* Default action if any action is passed in the URL
*/
define("DEFAULT_ACTION", "index");

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
		if (!isset($_GET["controller"])) {
			$_GET["controller"] = DEFAULT_CONTROLLER;
		}

		if (!isset($_GET["action"])) {
			$_GET["action"] = DEFAULT_ACTION;
		}

		// Here is where the "magic" occurs.
		// URLs like: index.php?controller=posts&action=add
		// will provoke a call to: new PostsController()->add()

		// Instantiate the corresponding controller
		$controller = loadController($_GET["controller"]);

		// Call the corresponding action
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
