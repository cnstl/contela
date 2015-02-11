<?php
class Session extends _Model {

  private static $data = array(), $in = false, $id = NULL;
  protected $sql = array(
    'get_login_data' => 'SELECT * FROM users WHERE (username = :u OR email = :u) AND password = :p',
    'set_lastvisit' => 'UPDATE users SET lastvisit = :time WHERE id = :id'
  );

  function __construct() {
    parent::__construct();
    session_start();
    if (isset($_POST['username'], $_POST['password'])) {
      $u = $_POST['username'];
      $p = self::hash($_POST['password']);
      if (isset($_SESSION['lastvisit'])) {
        unset($_SESSION['lastvisit']);
      }
    } elseif (isset($_SESSION['username'], $_SESSION['password'])) {
      $u = $_SESSION['username'];
      $p = $_SESSION['password'];
    }

    if (isset($u, $p)) {
      self::$data = self::get_login_data(array('u' => $u, 'p' => $p))[0];
      if (!empty(self::$data)) {
        // set the login flag
        self::$in = true;
        self::$id = self::$data->id;
        // set session data if not set
        if (!isset($_SESSION['username'])) {
          $_SESSION['username'] = self::$data->username;
          $_SESSION['password'] = self::$data->password;
        }
        // obscure the password
        unset(self::$data->password);
        // set lastvisit in _SESSION or in db
        if (!isset($_SESSION['lastvisit'])) {
          $_SESSION['lastvisit'] = self::$data->lastvisit;
          self::set_lastvisit(array('id' => self::$data->id, 'time' => time()));
        }
      }
    }
  }

  function logout() {
    session_start();
    self::$data = array();
    self::$in = false;
    self::$id = NULL;
    $_SESSION = array();
    session_destroy();
  }

  function get_data() {
    return self::$data;
  }

  public static function logged_in() {
    return self::$in;
  }

  public static function get_id() {
    return self::$id;
  }

	function hash($word) {
    return hash("sha256", $word);
  }
}

// provide basic session management

if (isset($_GET['logout'])) {
  Session::logout();
}

$session = new Session;
