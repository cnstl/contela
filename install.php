<?php
/* Basic Contela installer - try to make a config.json file and populate a database
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

if (isset($_POST['commit'])) {

$addr = empty($_POST['cfg']) ? 'config.json' : $_POST['cfg'];

$config_php = <<<end_of_config
<?php
/**
 * Contela
 * If you see this in a browser, I should probably shit bricks. Don't hit me with a frying pan.
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

_Controller::$config = json_decode(file_get_contents('{json address}'));
end_of_config;

$cfg_table = array(
  'db' => array(
  
  ),
  'title' => array(empty($_POST['title']) ? 'Contela' : $_POST['title']),
  'url' => empty($_POST['url']) ? dirname($_SERVER['PHP_SELF']) : $_POST['title']
);
  if ($cfg_table['url'] == '.')
    $cfg_table['url'] = '/';
  else
    $cfg_table['url'] .= '/';

  $cfg = str_replace('{json address}', $addr);
  file_put_contents('core/config.php', $cfg);

  file_put_contents
  // TODO do the json.
  // TODO do the db.

  echo "Committed";

} elseif (file_exists('core/config.php')) {
  echo "Already installed &ndash; delete config.php and config.json files before reinstalling";
} else {
?>
<!-- TODO FORM -->
<form action="install.php">

</form>
<?php
}
