<?php
/**
 * PHP Light MVC.
 * Extremely small but powerful MVC framework for PHP.
 * @package PHP Light MVC
 * @version 1.5.1
 * @author Sławomir Kokłowski {@link http://www.kurshtml.edu.pl}
 * @copyright Do NOT remove this comment!
 * @license LGPL
 */

/**
 * Auto load function.
 * To autoload class dir_subdir_ClassName, put it in a file "./dir/subdir/ClassName.php" or "./dir/subdir/ClassName.class.php".
 * Base directory is current directory with this __autoload function.
 * If class was not found "404 Not Found" HTTP header would be sent and script would exit.
 * @param string $className Name of class to autoload
 */
function __autoload($className)
{
    if (preg_match('/^[a-z][0-9a-z]*(_[0-9a-z]+)*$/i', $className))
    {
        $file = realpath(dirname(__FILE__).'/..') . '/' . str_replace('_' , '/', $className);
        if (file_exists($path = $file . '.php') || file_exists($path = $file . '.class.php'))
        {
            require_once $path;
            return;
        }
    }/* TODO: namespaces?!
    elseif (preg_match('/^[a-z][0-9a-z]*(\\\\[0-9a-z]+)*$/i', $className))
    {
        $file = dirname(__FILE__) . '/' . str_replace('\\' , '/', $className);
        if (file_exists($path = $file . '.php') || file_exists($path = $file . '.class.php'))
        {
            require_once $path;
            return;
        }
    }*/
    _Controller::http404();
}

/**
 * Model.
 * Requirements:
 * - PDO library.
 */
abstract class _Model
{
    /**
     * Data Source Name.
     * @static string
     */
    static $dsn;

    /**
     * Database username.
     * @static string
     */
    static $user;

    /**
     * Database password.
     * @static string
     */
    static $password;

    /**
     * Database connection.
     * Singleton.
     * @static PDO
     */
    protected static $db;

    /**
     * DVO class name.
     * @var string
     */
    protected $className;

    /**
     * SQL array.
     * To implement method i.e. $this->getItems() set "getItems" key and write prepared statement SQL as value.
     * @var array
     */
    protected $sql = array();

    private $sth = array();

    /**
     * Constructor.
     */
    function __construct()
    {
        if (empty(self::$db) && !empty(self::$dsn))
        {
          if (isset(self::$user, self::$password)) {
            self::$db = new PDO(self::$dsn, self::$user, self::$password);
          } else {
            self::$db = new PDO(self::$dsn);
          }
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /**
     * Executes database prepared statement.
     * Normally it is not required to call this method, because it is caled internally.
     * @param string $name Key name from $this->sql array
     * @param array $arguments Parameters to fill in prepered statement
     * @return array Result
     */
    protected function execute($name, $arguments=array())
    {
        if (!array_key_exists($name, $this->sql)) throw new Exception('Execution of undefined sql ' . $name);
        if (!array_key_exists($name, $this->sth)) $this->sth[$name] = self::$db->prepare($this->sql[$name]);
        foreach ($arguments as $key => $value)
        {
          switch (gettype($value))
          {
            case 'boolean':
              $type = PDO::PARAM_BOOL;
              break;
            case 'integer':
              $type = PDO::PARAM_INT;
              break;
            case 'NULL':
              $type = PDO::PARAM_NULL;
              break;
            default:
              $type = PDO::PARAM_STR;
          }
          $this->sth[$name]->bindValue($key, $value, $type);
        }
        $this->sth[$name]->execute();
        $result = array();
        if (preg_match('/^[^A-Z_]*SELECT[^A-Z_]/i', $this->sql[$name]))
        {
            while (($object = $this->className ? $this->sth[$name]->fetchObject($this->className) : $this->sth[$name]->fetchObject())) $result[] = $object;
        }
        else
        {
            $object = (object)array('count' => $this->sth[$name]->rowCount());
            if (preg_match('/^[^A-Z_]*(INSERT|REPLACE)[^A-Z_]/i', $this->sql[$name])) $object->id = self::$db->lastInsertId();
            $result[] = $object;
        }
        return $result;
    }

    function __call($name, $arguments)
    {
        if (!array_key_exists($name, $this->sql)) throw new Exception('Call to undefined method ' . get_class($this) . '::' . $name . '()');
        return $this->execute($name, array_key_exists(0, $arguments) ? $arguments[0] : array());
    }
}

/**
 * View.
 * Usually it isn't necessary to extend this class.
 * To visualize template print object itself.
 * In template file you have access to special variables:
 * - array $_config: Configuration reference,
 * - string $_dir: Base directory with template files.
 */
class _View
{
    /**
     * Base directory with template files.
     * @static string
     */
    static $dir = '';

    /**
     * Language strings
     * @static object
     */
    static $lang;

    /**
     * Global variables passed to every template file.
     * @static array
     */
    static $var = array();

    /**
     * Template file name.
     * @var string
     */
    protected $file;

    /**
     * Template assigned variables.
     * @var array
     */
    protected $data = array();

    /**
     * Constructor.
     * @param string $file Template file name.
     */
    function __construct($file)
    {
        $this->file = $file;
    }

    function __get($name)
    {
        return array_key_exists($name, $this->data) ? $this->data[$name] : null;
    }

    function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    function __toString()
    {
        if (!file_exists(self::$dir . $this->file)) return '';
        foreach (array_merge(self::$var, $this->data) as $name => $value)
        {
            if ($name != 'this') $$name = $value;
        }
        unset($name, $value);
        $_config = _Controller::$config;
        $_lang = self::$lang;
        $_dir = self::$dir;
        ob_start();
        require $_dir . $this->file;
        _Controller::$config = $_config;
        return ob_get_clean();
    }
}

/**
 * Controller.
 */
abstract class _Controller
{
    /**
     * Confiruration data.
     * @var static
     */
    static $config;

    /**
     * 404 Not Found action.
     * Sends HTTP header and exits.
     */
    static function http404()
    {
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    /**
     * HTTP redirect action.
     * Sends HTTP header and exits.
     * @param string $location Absolute or relative URL
     * @param int $status HTTP response status code
     */
    static function httpRedirect($location, $status=302)
    {
        $location = preg_replace(array('/^([^\r\n]+)/', '/(^|\/)\.(\/|$)/', '/[^\/]*\/\.\.(\/|$)/'), array('$1', '$1', ''), $location);
        header('Location: ' . (preg_match('/^[0-9a-z.+-]+:/i', $location) ? '' : 'http://' . $_SERVER['SERVER_NAME'] . (preg_match('/^\//', $location) ? '' : rTrim(dirName($_SERVER['SCRIPT_NAME']), '/') . '/')) . $location, true, $status);
        exit;
    }

    function __call($name, $arguments)
    {
        self::http404();
    }
}
