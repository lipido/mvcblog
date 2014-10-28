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
    
    if (isset($_POST["id"])) {      
      
      //load post    
      $postid = $_POST["id"];
      $post = $this->postmapper->findById($postid);    
      if ($post == NULL) {
	throw new Exception("no such post with id: ".$postid);
      }
      
      $comment = new Comment();
      $comment->setContent($_POST["content"]);
      $comment->setAuthor($this->currentUser);
      $comment->setPost($post);
      
      try {
	$comment->checkIsValidForCreate();
	$this->commentmapper->save($comment);
	
	$this->view->setFlash("Comment \"".$post ->getTitle()."\" successfully added.");
	
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
      }
    } else {
      throw new Exception("No such post id");
    }
    
    $this->view->redirect("posts", "view", "id=".$post->getId());
  }
  
}
