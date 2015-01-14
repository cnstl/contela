<?php
/* Basic Contela installer - try to make a config.json file and populate a database
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

$addr = 'config.json';
$cfg = <<<end_of_cfg
<?php
/**
 * Contela
 * If you see this in a browser, I should probably shit bricks. Don't hit me with a frying pan.
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

_Controller::$config = json_decode(file_get_contents('{json address}'));
end_of_cfg;


$cfg = str_replace('{json address}', $addr);
file_put_contents($addr, $cfg);
