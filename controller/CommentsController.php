<?php
//file: /controller/CommentsController.php

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Post.php");
require_once(__DIR__."/../model/Comment.php");

require_once(__DIR__."/../model/PostMapper.php");
require_once(__DIR__."/../model/CommentMapper.php");

require_once(__DIR__."/../controller/BaseController.php");

/**
 * Class CommentsController
 * 
 * Controller for comments related use cases.
 * 
 * @author lipido <lipido@gmail.com>
 */
class CommentsController extends BaseController {
  
  /**
   * Reference to the CommentMapper to interact
   * with the database
   * 
   * @var CommentMapper
   */
  private $commentmapper;
  
  /**
   * Reference to the PostMapper to interact
   * with the database
   * 
   * @var PostMapper
   */
  private $postmapper;  
  
  public function __construct() {
    parent::__construct();
    
    $this->commentmapper = new CommentMapper();
    $this->postmapper = new PostMapper();
  }
  
  /**
   * Action to adds a comment to a post
   *
   * This method should only be called via HTTP POST.
   *
   * The user of the comment is taken from the {@link BaseController::currentUser}
   * property.
   * The expected HTTP parameters are:
   * <ul>
   * <li>id: Id of the post (via HTTP POST)</li>
   * <li>content: Content of the comment (via HTTP POST)</li>
   * </ul>
   *
   * The views are:
   * <ul>
   * <li>posts/view?id=post: If comment was successfully added of, 
   * or if it was not validated (via redirect). Includes these view variables:</li>
   * <ul>   
   *  <li>errors (flash): Array including per-field validation errors</li>
   *  <li>comment (flash): The current Comment instance, empty or being added</li>   
   * </ul>
   * </ul>
   *
   * @return void
   */
  public function add() {
    if (!isset($this->currentUser)) {
      throw new Exception("Not in session. Adding posts requires login");
    }
    
    if (isset($_POST["id"])) { // reaching via HTTP Post...
      
      // Get the Post object from the database
      $postid = $_POST["id"];
      $post = $this->postmapper->findById($postid);
      
      // Does the post exist?      
      if ($post == NULL) {
	throw new Exception("no such post with id: ".$postid);
      }
      
      // Create and populate the Comment object
      $comment = new Comment();
      $comment->setContent($_POST["content"]);
      $comment->setAuthor($this->currentUser);
      $comment->setPost($post);
      
      try {
      
	// validate Comment object
	$comment->checkIsValidForCreate(); // if it fails, ValidationException
	
	// save the Comment object into the database
	$this->commentmapper->save($comment);
	
	// POST-REDIRECT-GET
	// Everything OK, we will redirect the user to the list of posts
	// We want to see a message after redirection, so we establish
	// a "flash" message (which is simply a Session variable) to be
	// get in the view after redirection.
	$this->view->setFlash("Comment \"".$post ->getTitle()."\" successfully added.");
	
	// perform the redirection. More or less: 
	// header("Location: index.php?controller=posts&action=view&id=$postid")
	// die();
	$this->view->redirect("posts", "view", "id=".$post->getId());
      }catch(ValidationException $ex) {
	$errors = $ex->getErrors();
	
	// Go back to the form to show errors.
	// However, the form is not in a single page (comments/add)
	// It is in the View Post page.
	// We will save errors as a "flash" variable (third parameter true)
	// and redirect the user to the referring page
	// (the View post page)	
	$this->view->setVariable("comment", $comment, true);
	$this->view->setVariable("errors", $errors, true);
	
	$this->view->redirect("posts", "view", "id=".$post->getId());
      }
    } else {
      throw new Exception("No such post id");
    }
  }  
}
