<?php
//file: controller/PostController.php

require_once(__DIR__."/../model/Comment.php");
require_once(__DIR__."/../model/Post.php");
require_once(__DIR__."/../model/PostMapper.php");
require_once(__DIR__."/../model/User.php");

require_once(__DIR__."/../core/ViewManager.php");
require_once(__DIR__."/../controller/BaseController.php");

/**
 * Class PostsController
 * 
 * Controller to make a CRUDL of Posts entities
 * 
 * @author lipido <lipido@gmail.com>
 */
class PostsController extends BaseController {
  
  /**
   * Reference to the PostMapper to interact
   * with the database
   * 
   * @var PostMapper
   */
  private $postMapper;  
  
  public function __construct() { 
    parent::__construct();
    
    $this->postMapper = new PostMapper();          
  }
  
  /**
   * Action to list posts
   * 
   * Loads all the posts from the database.
   * No HTTP parameters are needed.
   * 
   * The views are:
   * <ul>
   * <li>posts/index (via include)</li>   
   * </ul>
   */
  public function index() {  
    $posts = $this->postMapper->findAll();    
    
    $this->view->setVariable("posts", $posts);    
    
    $this->view->render("posts", "index");
  }
  
  /**
   * Action to view a given post
   * 
   * This action should only be called via GET
   * 
   * The expected HTTP parameters are:
   * <ul>
   * <li>id: Id of the post (via HTTP GET)</li>   
   * </ul>
   * 
   * The views are:
   * <ul>
   * <li>posts/view: If post is successfully loaded (via include).  Includes these view variables:</li>
   * <ul>
   *  <li>post: The current Post retrieved</li>
   *  <li>comment: The current Comment instance, empty or 
   *  being added (but not validated)</li>
   * </ul>
   * </ul>
   * 
   * @throws Exception If no such post of the given id is found
   * @return void
   * 
   */
  public function view(){
    if (!isset($_GET["id"])) {
      throw new Exception("id is mandatory");
    }
    
    $postid = $_GET["id"];
    $post = $this->postMapper->findByIdWithComments($postid);
    
    if ($post == NULL) {
      throw new Exception("no such post with id: ".$postid);
    }
    
    $this->view->setVariable("post", $post);
    
    // check if comment is already on the view (for example as flash variable)
    // if not, put an empty Comment for the view
    $comment = $this->view->getVariable("comment"); 
    $this->view->setVariable("comment", ($comment==NULL)?new Comment():$comment);
    
    $this->view->render("posts", "view");
    
  }
  
  /**
   * Action to add a new post
   * 
   * When called via GET, it shows the add form
   * When called via POST, it adds the post to the
   * database
   * 
   * The expected HTTP parameters are:
   * <ul>
   * <li>title: Title of the post (via HTTP POST)</li>
   * <li>content: Content of the post (via HTTP POST)</li>      
   * </ul>
   * 
   * The views are:
   * <ul>
   * <li>posts/add: If this action is reached via HTTP GET (via include)</li>   
   * <li>posts/index: If post was successfully added (via redirect)</li>
   * <li>posts/add: If validation fails (via include). Includes these view variables:</li>
   * <ul>
   *  <li>post: The current Post instance, empty or 
   *  being added (but not validated)</li>
   *  <li>errors: Array including per-field validation errors</li>   
   * </ul>
   * </ul>
   * @throws Exception if no user is in session
   * @return void
   */
  public function add() {
    if (!isset($this->currentUser)) {
      throw new Exception("Not in session. Adding posts requires login");
    }
    
    $post = new Post();
    
    if (isset($_POST["submit"])) {
      $post->setTitle($_POST["title"]);
      $post->setContent($_POST["content"]);
      $post->setAuthor($this->currentUser);
			 
      try {
	$post->checkIsValidForCreate();
	$this->postMapper->save($post);
	
	$this->view->setFlash("Post \"".$post ->getTitle()."\" successfully added.");
	
	$this->view->redirect("posts", "index");	
	
      }catch(ValidationException $ex) {
	$errors = $ex->getErrors();
	$this->view->setVariable("errors", $errors);
      }
    }
    
    $this->view->setVariable("post", $post);
    
    $this->view->render("posts", "add");
    
  }
  
  /**
   * Action to edit a post
   * 
   * When called via GET, it shows an edit form
   * including the current data of the Post.
   * When called via POST, it modifies the post in the
   * database.
   * 
   * The expected HTTP parameters are:
   * <ul>
   * <li>id: Id of the post (via HTTP POST and GET)</li>   
   * <li>title: Title of the post (via HTTP POST)</li>
   * <li>content: Content of the post (via HTTP POST)</li>      
   * </ul>
   * 
   * The views are:
   * <ul>
   * <li>posts/index: If post was successfully edited (via redirect)</li>
   * <li>posts/edit: If validation fails (via include). Includes these view variables:</li>
   * <ul>
   *  <li>post: The current Post instance, empty or being added (but not validated)</li>
   *  <li>errors: Array including per-field validation errors</li>   
   * </ul>
   * </ul>
   * @throws Exception if no id was provided
   * @throws Exception if no user is in session
   * @throws Exception if there is not any post with the provided id
   * @throws Exception if the current logged user is not the author of the post
   * @return void
   */  
  public function edit() {
    if (!isset($_REQUEST["id"])) {
      throw new Exception("A post id is mandatory");
    }
    
    if (!isset($this->currentUser)) {
      throw new Exception("Not in session. Editing posts requires login");
    }
    
    //load post    
    $postid = $_REQUEST["id"];
    $post = $this->postMapper->findById($postid);    
    if ($post == NULL) {
      throw new Exception("no such post with id: ".$postid);
    }
    
    if ($post->getAuthor() != $this->currentUser) {
      throw new Exception("logged user is not the author of the post id ".$postid);
    }
    
    if (isset($_POST["submit"])) {
      $post->setTitle($_POST["title"]);
      $post->setContent($_POST["content"]);
      
      try {
	//validate post
	$post->checkIsValidForUpdate();
	
	$this->postMapper->update($post);
	
	$this->view->setFlash(sprintf(i18n("Post \"%s\" successfully updated."),$post ->getTitle()));
	
	$this->view->redirect("posts", "index");	
	
      }catch(ValidationException $ex) {
	$errors = $ex->getErrors();
	$this->view->setVariable("errors", $errors);
      }
    }
    
    $this->view->setVariable("post", $post);
    
    $this->view->render("posts", "edit");    
  }
  
  /**
   * Action to delete a post
   * 
   * This action should only be called via HTTP POST
   * 
   * The expected HTTP parameters are:
   * <ul>
   * <li>id: Id of the post (via HTTP POST)</li>   
   * </ul>
   * 
   * The views are:
   * <ul>
   * <li>posts/index: If post was successfully deleted (via redirect)</li>
   * </ul>
   * @throws Exception if no id was provided
   * @throws Exception if no user is in session
   * @throws Exception if there is not any post with the provided id
   * @throws Exception if the author of the post to be deleted is not the current user
   * @return void
   */    
  public function delete() {  
    if (!isset($_POST["id"])) {
      throw new Exception("id is mandatory");
    }
    if (!isset($this->currentUser)) {
      throw new Exception("Not in session. Editing posts requires login");
    }
    
    //load post    
    $postid = $_REQUEST["id"];
    $post = $this->postMapper->findById($postid);    
    if ($post == NULL) {
      throw new Exception("no such post with id: ".$postid);
    }        
    if ($post->getAuthor() != $this->currentUser) {
      throw new Exception("Post author is not the logged user");
    }
    $this->postMapper->delete($post);
    
    $this->view->setFlash("Post \"".$post ->getTitle()."\" successfully deleted.");
    
    $this->view->redirect("posts", "index");
    
  }
  
}
