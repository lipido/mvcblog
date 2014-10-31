<?php
// file: model/User.php

require_once(__DIR__."/../core/ValidationException.php");

/**
 * Class User
 * 
 * Represents a User in the blog
 * 
 * @author lipido <lipido@gmail.com>
 */
class User {

  /**
   * The user name of the user
   * @var string
   */
  private $username;

  /**
   * The password of the user
   * @var string
   */
  private $passwd;
  
  /**
   * The constructor
   * 
   * @param string $username The name of the user
   * @param string $passwd The password of the user
   */
  public function __construct($username=NULL, $passwd=NULL) {
    $this->username = $username;
    $this->passwd = $passwd;    
  }

  /**
   * Gets the username of this user
   * 
   * @return string The username of this user
   */  
  public function getUsername() {
    return $this->username;
  }

  /**
   * Sets the username of this user
   * 
   * @param string $username The username of this user
   * @return void
   */  
  public function setUsername($username) {
    $this->username = $username;
  }
  
  /**
   * Gets the password of this user
   * 
   * @return string The password of this user
   */  
  public function getPasswd() {
    return $this->passwd;
  }  
  /**
   * Sets the password of this user
   * 
   * @param string $passwd The password of this user
   * @return void
   */    
  public function setPassword($passwd) {
    $this->passwd = $passwd;
  }
  
  /**
   * Checks if the current user instance is valid
   * for being registered in the database
   * 
   * @throws ValidationException if the instance is
   * not valid
   * 
   * @return void
   */  
  public function checkIsValidForRegister() {
      $errors = array();
      if (strlen($this->username) < 5) {
	$errors["username"] = "Username must be at least 5 characters length";
	
      }
      if (strlen($this->passwd) < 5) {
	$errors["passwd"] = "Password must be at least 5 characters length";	
      }
      if (sizeof($errors)>0){
	throw new ValidationException($errors, "user is not valid");
      }
  } 
}