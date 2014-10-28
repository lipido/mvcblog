<?php
// file: model/Post.php

require_once(__DIR__."/../core/ValidationException.php");

/**
 * Class Post
 * 
 * Represents a Post in the blog. A Post was written by an
 * specific User (author) and contains a list of Comments
 * 
 * @author lipido <lipido@gmail.com>
 */
class Post {

  /**
   * The id of this post
   * @var string
   */
  private $id;
  
  /**
   * The title of this post
   * @var string
   */  
  private $title;
  
  /**
   * The content of this post
   * @var string
   */    
  private $content;
  
  /**
   * The author of this post
   * @var User
   */    
  private $author;
  
  /**
   * The list of comments of this post
   * @var mixed
   */    
  private $comments;
  
  /**
   * The constructor
   * 
   * @param string $id The id of the post
   * @param string $title The id of the post   
   * @param string $content The content of the post
   * @param User $author The author of the post
   * @param mixed $comments The list of comments
   */  
  public function __construct($id=NULL, $title=NULL, $content=NULL, User $author=NULL, array $comments=NULL) {
    $this->id = $id;
    $this->title = $title;
    $this->content = $content;
    $this->author = $author;
    $this->comments = $comments;
    
  }

  /**
   * Gets the id of this post
   * 
   * @return string The id of this post
   */     
  public function getId() {
    return $this->id;
  }
  
  /**
   * Gets the title of this post
   * 
   * @return string The title of this post
   */     
  public function getTitle() {
    return $this->title;
  }
  
  /**
   * Sets the title of this post
   * 
   * @param string $title the title of this post
   * @return void
   */    
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * Gets the content of this post
   * 
   * @return string The content of this post
   */    
  public function getContent() {
    return $this->content;
  }

  /**
   * Sets the content of this post
   * 
   * @param string $content the content of this post
   * @return void
   */  
  public function setContent($content) {
    $this->content = $content;
  }

  /**
   * Gets the author of this post
   * 
   * @return User The author of this post
   */    
  public function getAuthor() {
    return $this->author;
  }
  
  /**
   * Sets the author of this post
   * 
   * @param User $author the author of this post
   * @return void
   */    
  public function setAuthor(User $author) {
    $this->author = $author;
  }

  /**
   * Gets the list of comments of this post
   * 
   * @return mixed The list of comments of this post
   */  
  public function getComments() {
    return $this->comments;
  }
  
  /**
   * Sets the comments of the post
   * 
   * @param mixed $comments the comments list of this post
   * @return void
   */  
  public function setComments(array $comments) {
    $this->comments = $comments;
  }

  /**
   * Checks if the current instance is valid
   * for being updated in the database.
   * 
   * @throws ValidationException if the instance is
   * not valid
   * 
   * @return void
   */    
  public function checkIsValidForCreate() {
      $errors = array();
      if (strlen(trim($this->title)) == 0 ) {
	$errors["title"] = "title is mandatory";
      }
      if (strlen(trim($this->content)) == 0 ) {
	$errors["content"] = "content is mandatory";
      }
      if ($this->author == NULL ) {
	$errors["author"] = "author is mandatory";
      }
      
      if (sizeof($errors) > 0){
	throw new ValidationException($errors, "post is not valid");
      }
  }

  /**
   * Checks if the current instance is valid
   * for being updated in the database.
   * 
   * @throws ValidationException if the instance is
   * not valid
   * 
   * @return void
   */
  public function checkIsValidForUpdate() {
    $errors = array();
    
    if (!isset($this->id)) {      
      $errors["id"] = "id is mandatory";
    }
    
    try{
      $this->checkIsValidForCreate();
    }catch(ValidationException $ex) {
      foreach ($ex->getErrors() as $key=>$error) {
	$errors[$key] = $error;
      }
    }    
    if (sizeof($errors) > 0) {
      throw new ValidationException($errors, "post is not valid");
    }
  }
}
