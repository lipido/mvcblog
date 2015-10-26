<?php
// file: model/PostMapper.php
require_once(__DIR__."/../core/PDOConnection.php");

require_once(__DIR__."/../model/User.php");
require_once(__DIR__."/../model/Post.php");
require_once(__DIR__."/../model/Comment.php");

/**
 * Class PostMapper
 *
 * Database interface for Post entities
 * 
 * @author lipido <lipido@gmail.com>
 */
class PostMapper {

  /**
   * Reference to the PDO connection
   * @var PDO
   */
  private $db;
  
  public function __construct() {
    $this->db = PDOConnection::getInstance();
  }

  /**
   * Retrieves all posts
   * 
   * Note: Comments are not added to the Post instances
   *
   * @throws PDOException if a database error occurs
   * @return mixed Array of Post instances (without comments)
   */  
  public function findAll() {   
    $stmt = $this->db->query("SELECT * FROM posts, users WHERE users.username = posts.author");    
    $posts_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
    $posts = array();
    
    foreach ($posts_db as $post) {
      $author = new User($post["username"]);
      array_push($posts, new Post($post["id"], $post["title"], $post["content"], $author));
    }   

    return $posts;
  }
  
  /**
   * Loads a Post from the database given its id
   * 
   * Note: Comments are not added to the Post
   *
   * @throws PDOException if a database error occurs
   * @return Post The Post instances (without comments). NULL 
   * if the Post is not found
   */    
  public function findById($postid){
    $stmt = $this->db->prepare("SELECT * FROM posts WHERE id=?");
    $stmt->execute(array($postid));
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($post != null) {
      return new Post(
	$post["id"],
	$post["title"],
	$post["content"],
	new User($post["author"]));
    } else {
      return NULL;
    }   
  }

  /**
   * Loads a Post from the database given its id
   * 
   * It includes all the comments
   *
   * @throws PDOException if a database error occurs
   * @return Post The Post instances (without comments). NULL 
   * if the Post is not found
   */      
  public function findByIdWithComments($postid){
    $stmt = $this->db->prepare("SELECT 
	P.id as 'post.id', 
	P.title as 'post.title',
	P.content as 'post.content', 
	P.author as 'post.author', 
	C.id as 'comment.id', 
	C.content as 'comment.content', 
	C.post as 'comment.post', 
	C.author as 'comment.author' 
	
	FROM posts P LEFT OUTER JOIN comments C 
	ON P.id = C.post
	WHERE
	P.id=? ");
    
    $stmt->execute(array($postid));
    $post_wt_comments= $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (sizeof($post_wt_comments) > 0) {
      $post = new Post($post_wt_comments[0]["post.id"], 
		       $post_wt_comments[0]["post.title"], 
		       $post_wt_comments[0]["post.content"],
		       new User($post_wt_comments[0]["post.author"]));
      $comments_array = array();
      if ($post_wt_comments[0]["comment.id"]!=null) {
        foreach ($post_wt_comments as $comment){
          $comment = new Comment( $comment["comment.id"],
                                  $comment["comment.content"],
                                  new User($comment["comment.author"]),
                                  $post);
          array_push($comments_array, $comment);
        }
      }
      $post->setComments($comments_array);
      
      return $post;
    }else {
      return NULL;
    }    
  }

  /**
   * Saves a Post into the database
   * 
   * @param Post $post The post to be saved
   * @throws PDOException if a database error occurs
   * @return void
   */    
  public function save(Post $post) {
    $stmt = $this->db->prepare("INSERT INTO posts(title, content, author) values (?,?,?)");
    $stmt->execute(array($post->getTitle(), $post->getContent(), $post->getAuthor()->getUsername()));    
  }

  /**
   * Updates a Post in the database
   * 
   * @param Post $post The post to be updated
   * @throws PDOException if a database error occurs
   * @return void
   */     
  public function update(Post $post) {
    $stmt = $this->db->prepare("UPDATE posts set title=?, content=? where id=?");
    $stmt->execute(array($post->getTitle(), $post->getContent(), $post->getId()));    
  }

  /**
   * Deletes a Post into the database
   * 
   * @param Post $post The post to be deleted
   * @throws PDOException if a database error occurs
   * @return void
   */   
  public function delete(Post $post) {
    $stmt = $this->db->prepare("DELETE from posts WHERE id=?");
    $stmt->execute(array($post->getId()));    
  }
  
}
