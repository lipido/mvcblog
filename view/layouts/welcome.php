<?php 
 // file: view/layouts/welcome.php
 
 require_once(__DIR__."/../../core/ViewManager.php");
 $view = ViewManager::getInstance();
 
?><!DOCTYPE html>
<html>
  <head>
    <title><?= $view->getVariable("title", "no title") ?></title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <?= $view->getFragment("css") ?>
    <?= $view->getFragment("javascript") ?>
  </head>
  <body> 
    <header>
      <h1><?= i18n("Welcome to the Blog App!") ?></h1>
    </header>
    <main>
      <!-- flash message -->
      <div id="flash">
	<?= $view->popFlash() ?>
      </div>
      <?= $view->getFragment(ViewManager::DEFAULT_FRAGMENT) ?>
    </main>
    <footer>
      <?php
      include(__DIR__."/language_select_element.php");
      ?>
    </footer>
  </body>
</html>