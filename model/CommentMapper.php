<?php
// file: model/CommentMapper.php

require_once(__DIR__."/../core/PDOConnection.php");

require_once(__DIR__."/../model/Comment.php");

/**
* Class CommentMapper
*
* Database interface for Comment entities
*
* @author lipido <lipido@gmail.com>
*/
class CommentMapper {

	/**
	* Reference to the PDO connection
	* @var PDO
	*/
	private $db;

	public function __construct() {
		$this->db = PDOConnection::getInstance();
	}

	/**
	* Saves a comment
	*
	* @param Comment $comment The comment to save
	* @throws PDOException if a database error occurs
	* @return int The new comment id
	*/
	public function save(Comment $comment) {
		$stmt = $this->db->prepare("INSERT INTO comments(content, author, post) values (?,?,?)");
		$stmt->execute(array($comment->getContent(), $comment->getAuthor()->getUsername(), $comment->getPost()->getId()));
		return $this->db->lastInsertId();
	}
}
