<?php 
 //file: view/users/register.php
 
 require_once(__DIR__."/../../core/ViewManager.php");
 $view = ViewManager::getInstance();
 $errors = $view->getVariable("errors");
 $user = $view->getVariable("user");
 $view->setVariable("title", "Register");
?>
<h1><?= i18n("Register")?></h1>
<form action="index.php?controller=users&amp;action=register" method="POST">
      <?= i18n("Username")?>: <input type="text" name="username" 
			value="<?= $user->getUsername() ?>">
      <?= isset($errors["username"])?$errors["username"]:"" ?><br>
      
      <?= i18n("Password")?>: <input type="password" name="passwd" 
			value="">
      <?= isset($errors["passwd"])?$errors["passwd"]:"" ?><br>
      
      <input type="submit" value="<?= i18n("Register")?>">
</form>
