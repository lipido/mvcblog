<?php
// file: model/Comment.php

require_once(__DIR__."/../core/ValidationException.php");

/**
* Class Comment
*
* Represents a Comment in the blog. A Comment is attached
* to a Post and was written by an specific User (author)
*
* @author lipido <lipido@gmail.com>
*/
class Comment {

	/**
	* The id of the comment
	* @var string
	*/
	private $id;

	/**
	* The content of the comment
	* @var string
	*/
	private $content;

	/**
	* The author of the comment
	* @var User
	*/
	private $author;

	/**
	* The post being commented by this comment
	* @var Post
	*/
	private $post;

	/**
	* The constructor
	*
	* @param string $id The id of the comment
	* @param string $content The content of the comment
	* @param User $author The author of the comment
	* @param Post $post The parent post
	*/
	public function __construct($id=NULL, $content=NULL, User $author=NULL, Post $post=NULL) {
		$this->id = $id;
		$this->content = $content;
		$this->author = $author;
		$this->post = $post;
	}

	/**
	* Gets the id of this comment
	*
	* @return string The id of this comment
	*/
	public function getId(){
		return $this->id;
	}

	/**
	* Gets the content of this comment
	*
	* @return string The content of this comment
	*/
	public function getContent() {
		return $this->content;
	}

	/**
	* Sets the content of the Comment
	*
	* @param string $content the content of this comment
	* @return void
	*/
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	* Gets the author of this comment
	*
	* @return User The author of this comment
	*/
	public function getAuthor() {
		return $this->author;
	}

	/**
	* Sets the author of this comment
	*
	* @param User $author the author of this comment
	* @return void
	*/
	public function setAuthor(User $author){
		$this->author = $author;
	}

	/**
	* Gets the parent post of this comment
	*
	* @return Post The parent post of this comment
	*/
	public function getPost() {
		return $this->post;
	}

	/**
	* Sets the parent Post
	*
	* @param Post $post the parent post
	* @return void
	*/
	public function setPost(Post $post) {
		$this->post = $post;
	}

	/**
	* Checks if the current instance is valid
	* for being inserted in the database.
	*
	* @throws ValidationException if the instance is
	* not valid
	*
	* @return void
	*/
	public function checkIsValidForCreate() {
		$errors = array();

		if (strlen(trim($this->content)) < 2 ) {
			$errors["content"] = "content is mandatory";
		}
		if ($this->author == NULL ) {
			$errors["author"] = "author is mandatory";
		}
		if ($this->post == NULL ) {
			$errors["post"] = "post is mandatory";
		}

		if (sizeof($errors) > 0){
			throw new ValidationException($errors, "comment is not valid");
		}
	}
}
