<?php 
 //file: view/posts/view.php
 require_once(__DIR__."/../../core/ViewManager.php");
 $view = ViewManager::getInstance();

 $post = $view->getVariable("post");
 $currentuser = $view->getVariable("currentusername"); 
 $newcomment = $view->getVariable("comment");  
 $errors = $view->getVariable("errors");
 
 $view->setVariable("title", "View Post");
 
?><h1><?= i18n("Post").": ".htmlentities($post->getTitle()) ?></h1>
    <em><?= sprintf(i18n("by %s"),$post->getAuthor()->getUsername()) ?></em>
    <p>
    <?= htmlentities($post->getContent()) ?>
    </p>

    <h2><?= i18n("Comments") ?></h2>    
    
    <?php foreach($post->getComments() as $comment): ?>
      <hr>
      <p><?= sprintf(i18n("%s commented..."),$comment->getAuthor()->getUsername()) ?> </p>
      <p><?= $comment->getContent(); ?></p>
    <?php endforeach; ?>
    
    <?php if (isset($currentuser) ): ?>    
    <h3><?= i18n("Write a comment") ?></h3>
    
    <form method="POST" action="index.php?controller=comments&amp;action=add">
      <?= i18n("Comment")?>:<br>
      <?= isset($errors["content"])?$errors["content"]:"" ?><br>
      <textarea type="text" name="content"><?= 
	    $newcomment->getContent();
      ?></textarea>
      <input type="hidden" name="id" value="<?= $post->getId() ?>" ><br>    
      <input type="submit" name="submit" value="<?=i18n("do comment") ?>">
    </form>
    
    <?php endif ?>