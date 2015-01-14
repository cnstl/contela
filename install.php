<?php
/* Basic Contela installer - try to make a config.json file and populate a database
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

if (file_exists('core/config.php')) {

  echo "Already installed &ndash; delete config.php and config.json files before reinstalling";

} elseif (isset($_POST['commit'])) {

// define JSON's address
$addr = empty($_POST['config_json']) ? 'config.json' : $_POST['config_json'];


// PHP start
$config_php = <<<end_of_config
<?php
/**
 * Contela
 * If you see this in a browser, I should probably shit bricks. Don't hit me with a frying pan.
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

_Controller::\$config = json_decode(file_get_contents('{json address}'));
end_of_config;
// PHP end


// JSON start
$config_json = array(
  'db' => array(
    'dsn' => empty($_POST['dsn']) ? 'sqlite:db' : $_POST['dsn']
  ),
  'title' => array(empty($_POST['title']) ? 'Contela' : $_POST['title']),
  'url' => empty($_POST['url']) ? dirname($_SERVER['PHP_SELF']) : $_POST['url']
);
if ($config_json['url'] == '.')
  $config_json['url'] = '/';
else
  $config_json['url'] .= '/';

if (!empty($_POST['dbuser'])) {
  $config_json['db']['user']     = $_POST['db_user'];
  $config_json['db']['password'] = $_POST['db_password'];
}
// JSON end


  // save the config
  file_put_contents('core/config.php', str_replace('{json address}', $addr, $config_php));
  file_put_contents($addr, json_encode($config_json));
  
  // it's done, populate db
  require_once './core/lightMVC.php';  // lightMVC framework
  require_once './core/config.php';    // config   loads config from file
  require_once './core/presets.php';   // presets  assigning the LightMVC variables based on config
  
  class DBPopulation extends _Model {
    protected $sql = array(
      'create_users' => 'CREATE TABLE users (
                           id INTEGER PRIMARY KEY,
                           username TEXT,
                           password TEXT,
                           email TEXT,
                           shownname TEXT,
                           avatar TEXT,
                           lastvisit INTEGER
                         );',
      'insert_admin' => 'INSERT INTO users VALUES (1, :name, :pass, :mail, :shown, "", 0)',
    );
  }
  $model = new DBPopulation;
  $model->create_users();
  $model->insert_admin([
          'name' => empty($_POST['admin_name']) ? 'admin'           : $_POST['admin_name'],
          'pass' => empty($_POST['admin_pass']) ? hash('sha256', 'admin') : hash('sha256', $_POST['admin_pass']),
          'mail' => empty($_POST['admin_mail']) ? 'admin@localhost' : $_POST['admin_mail'],
          'shown'=> empty($_POST['admin_shown'])? 'Administrator'   : $_POST['admin_shown']
          ]);

  echo "Committed";

} else {
?>
<!-- TODO FORM -->
<form action="install.php" method="POST">
  <p><input type="text" name="config_json" placeholder="JSON file address (e.g. ../configthatyouwontfind.json)"></p>
  <p><input type="text" name="dsn" placeholder="Data Source Name (e.g. sqlite:db)"></p>
  <p><input type="text" name="db_user" placeholder="DB user"></p>
  <p><input type="text" name="db_password" placeholder="DB password"></p>
  <p><input type="text" name="url" placeholder="Site URL"></p>
  <p><input type="text" name="title" placeholder="Site title"></p>
  <p><input type="text" name="admin_name" placeholder="Admin's username"></p>
  <p><input type="password" name="admin_pass" placeholder="Admin's password"></p>
  <p><input type="email" name="admin_mail" placeholder="Admin's e-mail"></p>
  <p><input type="text" name="admin_shown" placeholder="Admin's shown name"></p>
  <p><input name="commit" type="submit" value="Save the config & Install"></p>
</form>
<?php
}
