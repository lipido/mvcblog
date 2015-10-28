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
  
    // obtain the data from the database
    $posts = $this->postMapper->findAll();    
    
    // put the array containing Post object to the view
    $this->view->setVariable("posts", $posts);    
    
    // render the view (/view/posts/index.php)
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
    
    // find the Post object in the database
    $post = $this->postMapper->findByIdWithComments($postid);
    
    if ($post == NULL) {
      throw new Exception("no such post with id: ".$postid);
    }
    
    // put the Post object to the view
    $this->view->setVariable("post", $post);
    
    // check if comment is already on the view (for example as flash variable)
    // if not, put an empty Comment for the view
    $comment = $this->view->getVariable("comment"); 
    $this->view->setVariable("comment", ($comment==NULL)?new Comment():$comment);
    
    // render the view (/view/posts/view.php)
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
    
    if (isset($_POST["submit"])) { // reaching via HTTP Post...
      
      // populate the Post object with data form the form
      $post->setTitle($_POST["title"]);
      $post->setContent($_POST["content"]);
      
      // The user of the Post is the currentUser (user in session)
      $post->setAuthor($this->currentUser);
			 
      try {
	// validate Post object
	$post->checkIsValidForCreate(); // if it fails, ValidationException
	
	// save the Post object into the database
	$this->postMapper->save($post);
	
	// POST-REDIRECT-GET
	// Everything OK, we will redirect the user to the list of posts
	// We want to see a message after redirection, so we establish
	// a "flash" message (which is simply a Session variable) to be
	// get in the view after redirection.
	$this->view->setFlash(sprintf(i18n("Post \"%s\" successfully added."),$post ->getTitle()));
	
	// perform the redirection. More or less: 
	// header("Location: index.php?controller=posts&action=index")
	// die();
	$this->view->redirect("posts", "index");
	
      }catch(ValidationException $ex) {      
	// Get the errors array inside the exepction...
	$errors = $ex->getErrors();	
	// And put it to the view as "errors" variable
	$this->view->setVariable("errors", $errors);
      }
    }
    
    // Put the Post object visible to the view
    $this->view->setVariable("post", $post);    
    
    // render the view (/view/posts/add.php)
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
   * <li>posts/edit: If this action is reached via HTTP GET (via include)</li>
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
    
    
    // Get the Post object from the database
    $postid = $_REQUEST["id"];
    $post = $this->postMapper->findById($postid);
    
    // Does the post exist?
    if ($post == NULL) {
      throw new Exception("no such post with id: ".$postid);
    }
    
    // Check if the Post author is the currentUser (in Session)
    if ($post->getAuthor() != $this->currentUser) {
      throw new Exception("logged user is not the author of the post id ".$postid);
    }
    
    if (isset($_POST["submit"])) { // reaching via HTTP Post...  
    
      // populate the Post object with data form the form
      $post->setTitle($_POST["title"]);
      $post->setContent($_POST["content"]);
      
      try {
	// validate Post object
	$post->checkIsValidForUpdate(); // if it fails, ValidationException
	
	// update the Post object in the database
	$this->postMapper->update($post);
	
	// POST-REDIRECT-GET
	// Everything OK, we will redirect the user to the list of posts
	// We want to see a message after redirection, so we establish
	// a "flash" message (which is simply a Session variable) to be
	// get in the view after redirection.
	$this->view->setFlash(sprintf(i18n("Post \"%s\" successfully updated."),$post ->getTitle()));
	
	// perform the redirection. More or less: 
	// header("Location: index.php?controller=posts&action=index")
	// die();
	$this->view->redirect("posts", "index");	
	
      }catch(ValidationException $ex) {
	// Get the errors array inside the exepction...
	$errors = $ex->getErrors();
	// And put it to the view as "errors" variable
	$this->view->setVariable("errors", $errors);
      }
    }
    
    // Put the Post object visible to the view
    $this->view->setVariable("post", $post);
    
    // render the view (/view/posts/add.php)
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
    
     // Get the Post object from the database
    $postid = $_REQUEST["id"];
    $post = $this->postMapper->findById($postid);
    
    // Does the post exist?
    if ($post == NULL) {
      throw new Exception("no such post with id: ".$postid);
    }  
    
    // Check if the Post author is the currentUser (in Session)
    if ($post->getAuthor() != $this->currentUser) {
      throw new Exception("Post author is not the logged user");
    }
    
    // Delete the Post object from the database
    $this->postMapper->delete($post);
    
    // POST-REDIRECT-GET
    // Everything OK, we will redirect the user to the list of posts
    // We want to see a message after redirection, so we establish
    // a "flash" message (which is simply a Session variable) to be
    // get in the view after redirection.
    $this->view->setFlash(sprintf(i18n("Post \"%s\" successfully deleted."),$post ->getTitle()));
    
    // perform the redirection. More or less: 
    // header("Location: index.php?controller=posts&action=index")
    // die();
    $this->view->redirect("posts", "index");
    
  }  
}
