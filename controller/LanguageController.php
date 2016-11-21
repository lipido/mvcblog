<?php
//file: controller/LanguageController.php

require_once(__DIR__."/../core/I18n.php");

/**
* Class LanguageController
*
* Controller to manage the session language.
* Allows you to change the current language
* by establishing it in the I18n singleton instance
*
* @author lipido <lipido@gmail.com>
*/
class LanguageController {
	const LANGUAGE_SETTING = "__language__";

	/**
	* Action to change the current language
	*
	* The expected HTTP parameters are:
	* <ul>
	* <li>lang: lange to change to (via HTTP GET)</li>
	* </ul>
	* @return void
	*/
	public function change() {
		if(!isset($_GET["lang"])) {
			throw new Exception("no lang parameter was provided");
		}
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		I18n::getInstance()->setLanguage($_GET["lang"]);

		//go back to previous page
		header("Location: ".$_SERVER["HTTP_REFERER"]);
		die();
	}

	/**
	* Action get a Javascript script with a ji18n(key) function
	*
	* This is useful to translate strings inside your javascript (.js) files
	* You have to include a <script src="index.php?controller=languages&action=i18njs">
	* </script> tag before all your scripts (in the layout, for example).
	* @return void
	*/
	public function i18njs() {
		header('Content-type: application/javascript');
		echo "var i18nMessages = [];\n";
		echo "function ji18n(key) { if (key in i18nMessages) return i18nMessages[key]; else return key;}\n";
		foreach (I18n::getInstance()->getAllMessages() as $key=>$value) {
			echo "i18nMessages['$key'] = '$value';\n";
		}
}
}
