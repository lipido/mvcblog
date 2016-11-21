<?php
// file: /core/ViewManager.php

/**
* Class ViewManager
*
* This class implements the glue between the controller
* and the view.
*
* This class is a singleton. You should use getInstance()
* to get the view manager instance.
*
* The main responsibilities are:
*
* 1.Save variables from the controller and make them available
*	 for views. This includes "flash" variables, which are
*	 variables kept in session that are removed just after
*	 they are retrieved. Use methods like setVariable, getVariable and
*	 setFlash.
*
* 2.Render views. This basically performs an 'include' of the view
*	 file, but with more MVC-oriented parameters
*	 (controller name and view name).
*
* 3.Layout (or templating) system. Based on PHP output buffers
*	 (ob_ functions). Once the view manager is initialized,
*	 the output buffer is enabled. By default, all contents that are
*	 generated inside your views will be saved in a DEFAULT_FRAGMENT.
*	 The DEFAULT_FRAGMENT is normally used as the "main" content of
*	 the resulting layout. However, you can generate contents for
*	 other fragments that will go into the layout. For example, inside
*	 your views, you have to call moveToFragment(fragmentName) before
*	 generating content for a desired fragment. This fragment normally
*	 will be after retrieved by the layout (via calls to getFragment).
*	 Typical fragments are 'css', 'javascript', so you can specify
*	 additional css and javascripts from your specific views.
*
* @author lipido <lipido@gmail.com>
*/
class ViewManager {

	/**
	* key for the default fragment
	*
	* @var string
	*/
	const DEFAULT_FRAGMENT = "__default__";

	/**
	* Buffered contents accumulted per each fragment
	*
	* @var mixed
	*/
	private $fragmentContents = array();

	/**
	* Values of view variables
	*
	* @var mixed
	*/
	private $variables = array();

	/**
	* The current fragment name where output is being
	* accumulated
	*
	* @var string
	*/
	private $currentFragment = self::DEFAULT_FRAGMENT;

	/**
	* The name of the layout to be used in renderLayout
	*
	* @var string
	*/
	private $layout = "default";


	private function __construct() {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		ob_start();
	}

	/// BUFFER MANAGEMENT
	/**
	* Saves the contents of the output buffer into
	* the current fragment. Cleans the ouput buffer
	*
	* @return void
	*/
	private function saveCurrentFragment() {
		//save current fragment
		$this->fragmentContents[$this->currentFragment].=ob_get_contents();
		//clean output buffer
		ob_clean();
	}

	/**
	* Changes the current fragment where output is accumulating
	*
	* The current output is saved before changing.
	* The subsequent outputs will be accumulted in the specified
	* fragment.
	*
	* @param string $name The name of the fragment to move to
	* @return void
	*/
	public function moveToFragment($name) {
		//save the current fragment contents
		$this->saveCurrentFragment();
		$this->currentFragment = $name;
	}

	/**
	* Changes to the default fragment.
	*
	* The current output is saved before changing.
	* The subsequent outputs will be accumulated in the default fragment
	*
	* @return void
	*/
	public function moveToDefaultFragment(){
		$this->moveToFragment(self::DEFAULT_FRAGMENT);
	}

	/**
	* Gets the contents occumulated in an specified fragment
	*
	* @param string $fragment The fragment to retrieve the contents from
	* @param string $default The default content if the $fragment does
	* not exist
	* @return string The fragment contents
	*/
	public function getFragment($fragment, $default="") {
		if (!isset($this->fragmentContents[$fragment])) {
			return $default;
		}
		return $this->fragmentContents[$fragment];
	}

	/// VARIABLES MANAGEMENT

	/**
	* Establishes a variable for the view
	*
	* Variables could be also kept in session (via $flash parameter)
	*
	* @param string $varname The name of the variable
	* @param any $value The value of the variable
	* @param boolean $flash If the variable value shoud be kept
	* in session
	*/
	public function setVariable($varname, $value, $flash=false) {
		$this->variables[$varname] = $value;
		if ($flash==true) {
			//a flash variable, will be stored in session_start
			if(!isset($_SESSION["viewmanager__flasharray__"])) {
				$_SESSION["viewmanager__flasharray__"][ $varname]=$value;
				print_r($_SESSION["viewmanager__flasharray__"]);
			}else{
				$_SESSION["viewmanager__flasharray__"][$varname]=$value;
			}
		}
	}

	/**
	* Retrieves a previously established variable.
	*
	* If the variable is a flash variable, it removes it
	* from the session after being retrieved
	*
	* @param string $varname The name of the variable
	* @param $default The value of the variable to return
	* if the variable does not exists
	* @return any value of the variable
	*/
	public function getVariable($varname, $default=NULL) {
		if (!isset($this->variables[$varname])) {
			if (isset($_SESSION["viewmanager__flasharray__"])
			&& isset($_SESSION["viewmanager__flasharray__"][$varname])){
				$toret=$_SESSION["viewmanager__flasharray__"][$varname];
				unset($_SESSION["viewmanager__flasharray__"][$varname]);
				return $toret;
			}
			return $default;
		}
		return $this->variables[$varname];
	}

	/**
	* Establishes a flash message
	*
	* Flash messages are useful to pass text from one page to other
	* via HTTP redirects, sinde they are kept in session.
	*
	* @param string $flashMessage The message to save into session
	* @return void
	*/
	public function setFlash($flashMessage) {
		$this->setVariable("__flashmessage__", $flashMessage, true);

	}

	/**
	* Retrieves the flash message (and pops it)
	*
	* @return string The flash message
	*/
	public function popFlash() {
		return $this->getVariable("__flashmessage__", "");
	}


	/// RENDERING

	/**
	* Sets the layout to be used when renderLayout will be called
	*
	* @param string $layout The layout to use
	* @return void
	*/
	public function setLayout($layout) {
		$this->layout = $layout;
	}

	/**
	* Renders an specified view of a specified controller
	*
	* If the $controller=mycontroller and $view=myview, the
	* selected php file will be: view/mycontroller/myview.php
	*
	* It uses the the selected layout (via setLayout)
	* or the default layout if it was not specified before
	* calling the setLayout method
	*
	* @param string $controller Name of the controller (in URL format
	* e.g: "posts")
	* @param string $viewname Name of the view
	* @return void
	*/
	public function render($controller, $viewname) {
		include(__DIR__."/../view/$controller/$viewname.php");
		$this->renderLayout();
	}

	/**
	* Sends an HTTP 302 redirection to a given action
	* inside a controller
	*
	* @param string $controller The name of the controller
	* @param string $action The name of the action
	* @param string $queryString An optional query string
	* @return void
	*/
	public function redirect($controller, $action, $queryString=NULL) {
		header("Location: index.php?controller=$controller&action=$action".(isset($queryString)?"&$queryString":""));
		die();
	}

	/**
	* Sends an HTTP 302 redirection to the refererring page, which
	* is the page where the user was, just before making the current
	* request.
	*
	* @param string $queryString An optional query string
	* @return void
	*/
	public function redirectToReferer($queryString=NULL) {
		header("Location: ".$_SERVER["HTTP_REFERER"].(isset($queryString)?"&$queryString":""));
		die();
	}

	/**
	* Renders the layout
	*
	* It basically includes the /view/layouts/[layout].php.
	* Normally, inside the layout file, there will be calls to
	* retrieve fragment contents, especially the default fragment
	* contents.
	*/
	private function renderLayout() {
		// move to layout fragment so
		// all previously generated output contents
		// were saved in the $this->fragmentContents
		// array
		$this->moveToFragment("layout");

		// draw the layout. Inside the layout we use this
		// view manager to retrieve previously generated contents,
		// specially the DEFAULT_FRAGMENT (the main content)
		include(__DIR__."/../view/layouts/".$this->layout.".php");

		ob_flush();
	}

	// singleton
	private static $viewmanager_singleton = NULL;
	public static function getInstance() {
		if (self::$viewmanager_singleton == null) {
			self::$viewmanager_singleton = new ViewManager();
		}
		return self::$viewmanager_singleton;
	}

}


// force the first instantiation of the ViewManager
// since the buffered output will be needed including
// those cases where neither the controller nor the view get the instance of the viewmanager
ViewManager::getInstance();
