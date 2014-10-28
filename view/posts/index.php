<?php 
 //file: view/posts/index.php

 require_once(__DIR__."/../../core/ViewManager.php");
 $view = ViewManager::getInstance();
 
 $posts = $view->getVariable("posts");
 $currentuser = $view->getVariable("currentusername");
 
 $view->setVariable("title", "Posts");
 
?><h1><?=i18n("Posts")?></h1>

<table border="1">
      <tr>
	<th><?= i18n("Title")?></th><th><?= i18n("Author")?></th><th><?= i18n("Actions")?></th>
      </tr>
    
    <?php foreach ($posts as $post): ?>
	    <tr>	    
	      <td>
		    <a href="index.php?controller=posts&amp;action=view&amp;id=<?= $post->getId() ?>"><?= htmlentities($post->getTitle()) ?></a>
	      </td>
	      <td>
		<?= $post->getAuthor()->getUsername() ?>
	      </td>
	      <td>
		<?php
		//show actions ONLY for the author of the post (if logged)
		
		
		if (isset($currentuser) && $currentuser == $post->getAuthor()->getUsername()): ?>
		
		  <?php 
		  // 'Delete Button': show it as a link, but do POST in order to preserve
		  // the good semantic of HTTP
		  ?>
		  <form 		    
		    method="POST" 
		    action="index.php?controller=posts&amp;action=delete" 
		    id="delete_post_<?= $post->getId(); ?>"
		    style="display: inline" 
		    >
		  
		    <input type="hidden" name="id" value="<?= $post->getId() ?>">
		  
		    <a href="#" 
		      onclick="
		      if (confirm('<?= i18n("are you sure?")?>')) {
			    document.getElementById('delete_post_<?= $post->getId() ?>').submit() 
		      }"
		    ><?= i18n("Delete") ?></a>
		  
		  </form>
		  
		  &nbsp;
		  
		  <?php 
		  // 'Edit Button'
		  ?>		  
		  <a href="index.php?controller=posts&amp;action=edit&amp;id=<?= $post->getId() ?>"><?= i18n("Edit") ?></a>
		
		<?php endif; ?>

	      </td>
	    </tr>    
    <?php endforeach; ?>
    
    </table> 
     <?php if (isset($currentuser)): ?>
      <a href="index.php?controller=posts&amp;action=add"><?= i18n("Create post") ?></a>    
    <?php endif; ?>
    
