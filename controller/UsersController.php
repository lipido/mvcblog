<?php

require_once(__DIR__."/../core/ViewManager.php");
require_once(__DIR__."/../core/I18n.php");

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/UserMapper.php");

require_once(__DIR__."/../controller/BaseController.php");

/**
 * Class UsersController
 * 
 * Controller to login, logout and user registration
 * 
 * @author lipido <lipido@gmail.com>
 */
class UsersController extends BaseController {
  
  /**
   * Reference to the UserMapper to interact
   * with the database
   * 
   * @var UserMapper
   */  
  private $userMapper;    
  
  public function __construct() {    
    parent::__construct();
    
    $this->userMapper = new UserMapper();

    // Users controller operates in a "welcome" layout
    // different to the "default" layout where the internal
    // menu is displayed
    $this->view->setLayout("welcome");     
  }

 /**
   * Action to login
   * 
   * Logins a user checking its creedentials agains
   * the database   
   * 
   * When called via GET, it shows the login form
   * When called via POST, it tries to login
   * 
   * The expected HTTP parameters are:
   * <ul>
   * <li>login: The username (via HTTP POST)</li>
   * <li>passwd: The password (via HTTP POST)</li>      
   * </ul>
   *
   * The views are:
   * <ul>
   * <li>posts/index: If login succeds (via redirect)</li>   
   * <li>users/login: If validation fails (via include). Includes these view variables:</li>
   * <ul>   
   *  <li>errors: Array including validation errors</li>   
   * </ul>   
   * </ul>
   * 
   * @return void
   */
  public function login() {
    if (isset($_POST["username"])){
      //process login form    
      if ($this->userMapper->isValidUser($_POST["username"], 							   $_POST["passwd"])) {
	
	$_SESSION["currentuser"]=$_POST["username"];
	
	// send user to the restricted area (HTTP 302 code)
	$this->view->redirect("posts", "index");
	
      }else{
	$errors["general"] = "Username is not valid";
      }
    }       
    
    $this->view->render("users", "login");
    
  }

 /**
   * Action to register
   * 
   * When called via GET, it shows the register form.
   * When called via POST, it tries to add the user
   * to the database.
   * 
   * The expected HTTP parameters are:
   * <ul>
   * <li>login: The username (via HTTP POST)</li>
   * <li>passwd: The password (via HTTP POST)</li>      
   * </ul>
   *
   * The views are:
   * <ul>
   * <li>users/login: If login succeds (via redirect)</li>   
   * <li>users/register: If validation fails (via include). Includes these view variables:</li>
   * <ul>   
   *  <li>user: The current User instance, empty or being added
   *  (but not validated)</li>      
   *  <li>errors: Array including validation errors</li>   
   * </ul>   
   * </ul>
   * 
   * @return void
   */
  public function register() {
    
    $user = new User();
    
    if (isset($_POST["username"])){
      $errors = array();
      $user->setUsername($_POST["username"]);
      $user->setPassword($_POST["passwd"]);
      
      try{
	$user->checkIsValidForRegister();
	
	if (!$this->userMapper->usernameExists($_POST["username"])){
	  $this->userMapper->save($user);
	  $this->view->setFlash("Username ".$user->getUsername()." successfully added. Please login now");
	  
	  $this->view->redirect("users", "login");	  
	} else {
	  $errors["username"] = "Username already exists";
	  $this->view->setVariable("errors", $errors);
	}
      }catch(ValidationException $ex) {
	$errors = $ex->getErrors();	
	$this->view->setVariable("errors", $errors);
      }
    }
    
    $this->view->setVariable("user", $user);
    
    $this->view->render("users", "register");
    
  }

 /**
   * Action to logout
   * 
   * This action should be called via GET
   * 
   * No HTTP parameters are needed.
   *
   * The views are:
   * <ul>
   * <li>users/login (via redirect)</li>
   * 
   * @return void
   */
  public function logout() {
    session_destroy();
    
    $this->view->redirect("users", "login");
   
  }
  
}
