<?php
/**
 * PHP Class to user access (login, register, logout, etc)
 * 
 * <code><?php
 * include('access.class.php');
 * $user = new flexibleAccess();
 * ? ></code>
 * 
 * For support issues please refer to the webdigity forums :
 *				http://www.webdigity.com/index.php/board,91.0.html
 * or the official web site:
 *				http://phpUserClass.com/
 * ==============================================================================
 * 
 * @version $Id: access.class.php,v 0.93 2008/05/02 10:54:32 $
 * @copyright Copyright (c) 2007 Nick Papanotas (http://www.webdigity.com) and NanoTech
 * @author Nick Papanotas <nikolas@webdigity.com>, modifications by NanoTech
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * 
 * ==============================================================================

 */

/**
 * Flexible Access - The basic authentication class
 * 
 * @param string $dbTable
 */

class Access {

	/**
	 * The database table that holds all the information
	 * var string
	 */
	var $dbTable  = 'users';

	/**
	 * The session variable ($_SESSION[$sessionVariable]) which will hold the data while the user is logged on
	 * var string
	 */
	var $sessionVariable = 'userid';

	/**
	 * Those are the fields that our table uses in order to fetch the needed data. The structure is 'fieldType' => 'fieldName'
	 * var array
	 */
	var $tbFields = array(
		'userID'=> 'id', 
		'login' => 'username',
		'pass'  => 'password',
		'email' => 'email',
		'active'=> 'active'
	);

	/**
	 * When user wants the system to remember him/her, how much time to keep the cookie? (seconds)
	 * var int
	 */
	var $remTime = 1209600; // Two weeks

	/**
	 * The name of the cookie which we will use if user wants to be remembered by the system
	 * var string
	 */
	var $remCookieName;

	/**
	 * The cookie domain
	 * var string
	 */
	var $remCookieDomain = '';

	/**
	 * The method used to encrypt the password. It can be sha1, md5 or nothing (no encryption)
	 * var string
	 */
	var $passMethod = 'sha1';

	/**
	 * Display errors? Set this to true if you are going to seek for help, or have troubles with the script
	 * var bool
	 */
	var $displayErrors = true;

	/**
	 * Salt for hashing
	 */
	private $salt;
	private $cookiesalt;

	var $userID;
	var $dbConn;
	var $userData=array();

	/**
	 * Class Constructor
	 * 
	 * @return void
	 */
	function __construct($salt, $cookie_name, $cookie_salt)
	{
		global $Database;

		$this->remCookieDomain = $cookie_name;
		$this->salt = $salt;
		$this->cookiesalt = $cookie_salt;

		if (session_id() == '') {
			session_start();
		}

		// Set the cookie domain. This should be set to a fixed value when used in
		// a production enviornment.
		if(DEBUG == true)
			$this->remCookieDomain = $this->remCookieDomain == '' ? $_SERVER['HTTP_HOST'] : $this->remCookieDomain;

		// If a user is logged in, get their data.
		if(!empty($_SESSION[$this->sessionVariable]))
			$this->loadUser($_SESSION[$this->sessionVariable]);

		// Is there a cookie?
		if ( (isset($_COOKIE[$this->remCookieName]) ||
		      isset($_POST['session_id']))
			 && !isset($_SESSION['userdata'])
		   )
		{
			// Read the cookie
			$cookie = $this->readCookie();

			// Check if the cookie is in the database
			$result = $Database->get('*', 'authcookies', $cookie);

			if($result !== false) {
				$this->initUser($result[0]['userid']); // Log the user in.
				$this->makeCookie(); // Make a new cookie
			}
		}
	}

	/**
	 * Login function
	 * @param string $uname
	 * @param string $password
	 * @param bool $loadUser
	 * @return bool
	 */
	function login($uname, $password, $remember = false, $loadUser = true)
	{
		global $Database;
		$uname    = $Database->escape($uname);
		$password = $originalPassword = $Database->escape($password);

		$password = sha1($this->salt.$password);
		$userid = $Database->get('id', 'users', array('username' => $uname, 'password' => $password));
		$userid = (int) $userid[0]['id'];

		if ($userid == false) {
			return false;
		} else {
			if ($loadUser)
			{
				$this->initUser($userid);

				if($remember == true) $this->makeCookie();
			}

			return true;
		}
	}

	/**
	 * Logout function
	 * param string $redirectTo
	 * @return bool
	 */
	function logout($redirectTo = '')
	{
		global $Database;

		$cookie = $this->readCookie();
		$Database->delete($cookie, 'authcookies');
		setcookie($this->remCookieName, '');

		$_SESSION[$this->sessionVariable] = '';
		$_SESSION['userdata'] = false;
		$_SESSION['userid'] = false;

		$this->userData = '';

		session_destroy();

		if ( $redirectTo != '' && !headers_sent()){
			header('Location: '.$redirectTo );
			exit; // To ensure security
		}
	}

	function initUser($userid) {
		global $Database;

		$userdata = $Database->get('*', 'users', array('id' => (int) $userid));
		$userdata = $userdata[0];
		unset($userdata['password']);
		$this->userData = $userdata;
		$_SESSION['userdata'] = $userdata;

		$this->userID = $this->userData[$this->tbFields['userID']];
		$_SESSION[$this->sessionVariable] = $this->userID;

		unset($userdata);
		return true;
	}

	function readCookie()
	{
		if(isset($_COOKIE[$this->remCookieName]) || isset($_POST['session_id']))
		{
			if (isset($_COOKIE[$this->remCookieName])) {
				$cookie = $_COOKIE[$this->remCookieName];
			} else {
				$cookie = $_POST['session_id'];
			}

			$cookie = str_split($cookie, 40);

			$cookie = array(
				'token' => $cookie[0],
				'series' => $cookie[1],
				'userid' => $cookie[2]
			);

			return $cookie;

		} else return false;
	}

	function makeCookie($userid = 0) {
		global $Database;

		$userid = $userid == 0 ? $this->userID : $userid;

		$oldcookie = $this->readCookie();

		if($oldcookie != false) {
			$result = $Database->get('series', 'authcookies', array('series' => $oldcookie['series']));
			if($result != false)
				$cookie['series'] = $result[0]['series'];
		}

		if(!isset($cookie['series']))
			$cookie['series'] = sha1(uniqid(mt_rand(), true).$this->cookiesalt);

		$cookie['token'] = sha1(uniqid(mt_rand(), true).$this->cookiesalt);
		$cookie['userid'] = $userid;

		$Database->insert($cookie, 'authcookies');
		$Database->delete($oldcookie, 'authcookies');

		$cookie = $cookie['token'].$cookie['series'].$cookie['userid'];
		$_SESSION['authcookie'] = $cookie;

		setcookie($this->remCookieName, $cookie,
			time()+$this->remTime, '/', $this->remCookieDomain, false, true);

		return true;
	}

	/**
	 * Get a property of a user. You should give here the name of the field that you seek from the user table
	 * @param string $property
	 * @return string
	 */
	function get_property($property)
	{
		if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
		if (!isset($this->userData[$property])) $this->error('Unknown property <b>'.$property.'</b>', __LINE__);
		return $this->userData[$property];
	}

	/**
	 * Is the user an active user?
	 * @return bool
	 */
	function is_active()
	{
		return $this->userData[$this->tbFields['active']];
	}

	/**
	 * Is the user loaded?
	 * @ return bool
	 */
	function is_loaded()
	{
		return empty($this->userID) ? false : true;
	}

	/**
	 * Activates the user account
	 * @return bool
	 */
	function activate()
	{
		global $Database;
		if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
		if ( $this->is_active()) $this->error('Already active account', __LINE__);
		$res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 1 
			WHERE `{$this->tbFields['userID']}` = '".$Database->escape($this->userID)."' LIMIT 1");
		if (@mysql_affected_rows() == 1)
		{
			$this->userData[$this->tbFields['active']] = true;
			return true;
		}
		return false;
	}

	/*
	 * Creates a user account. The array should have the form 'database field' => 'value'
	 * @param array $data
	 * return int
	 */  
	function insertUser($data){
		global $Database;
		if (!is_array($data)) $this->error('Data is not an array', __LINE__);

		$data[$this->tbFields['pass']] = sha1($this->salt.$data[$this->tbFields['pass']]); 

		foreach ($data as $k => $v ) $data[$k] = $Database->escape($v);
		$result = $Database->insert($data, $this->dbTable);
		return (int) mysql_insert_id($Database->link);
	}

	/*
	 * Creates a random password. You can use it to create a password or a hash for user activation
	 * param int $length
	 * param string $chrs
	 * return string
	 */
	function randomPass($length=10, $chrs = '1234567890qwertyuiopasdfghjklzxcvbnm'){
		$pwd = '';
		for($i = 0; $i < $length; $i++) {
			$pwd .= $chrs{mt_rand(0, strlen($chrs)-1)};
		}
		return $pwd;
	}


	////////////////////////////////////////////
	// PRIVATE FUNCTIONS
	////////////////////////////////////////////

	/**
	 * A function that is used to load one user's data
	 * @access private
	 * @param string $userID
	 * @return bool
	 */
	private function loadUser($userID)
	{
		global $Database;
		$res = $Database->query("SELECT * FROM `{$this->dbTable}` WHERE `{$this->tbFields['userID']}` = '".$Database->escape($userID)."' LIMIT 1");
		if ( mysql_num_rows($res) == 0 )
			return false;
		$this->userData = mysql_fetch_array($res);
		$this->userID = (int) $userID;
		$_SESSION[$this->sessionVariable] = $this->userID;
		return true;
	}

	/**
	 * SQL query function
	 * Depreciated: Use database.class
	 *
	 * @access private
	 * @param string $sql
	 * @return string
	 */
	private function query($sql, $line = 'Unknown')
	{
		global $Database;
		//if (defined('DEVELOPMENT_MODE') ) echo '<b>Query to execute: </b>'.$sql.'<br /><b>Line: </b>'.$line.'<br />';
		//$res = mysql_db_query($this->dbName, $sql, $Database->link);
		$res = $Database->query($sql);
		if ( !$res )
			$this->error(mysql_error($Database->link), $line);
		return $res;
	}

	/**
	 * Produces the result of addslashes() with more safety
	 * Depreciated: Use database.class
	 *
	 * @access private
	 * @param string $str
	 * @return string
	 */  
	private function escape($str) {
		$str = get_magic_quotes_gpc()?stripslashes($str):$str;
		$str = mysql_real_escape_string($str);
		return $str;
	}

	/**
	 * Error holder for the class
	 * Depreciated: Use message.class
	 *
	 * @access private
	 * @param string $error
	 * @param int $line
	 * @param bool $die
	 * @return bool
	 */  
	private function error($error, $line = '', $die = false) {
		if ( $this->displayErrors )
			echo '<b>Error: </b>'.$error.'<br /><b>Line: </b>'.($line==''?'Unknown':$line).'<br />';
		if ($die) exit;
		return false;
	}
}
?>
