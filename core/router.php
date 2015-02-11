<?php
/**
 * Contela
 * If you see this in a browser, I should probably shit bricks. Don't hit me with a frying pan.
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

// if no module is set, default module is extracted from config or else a default "landing" module is used.
if (empty($_GET['module'])) {
  $_GET['module'] = !empty(_Controller::$config->home->module) ? _Controller::$config->home->module : 'landing';
  if (empty($_GET['action']))
    $_GET['action'] = !empty(_Controller::$config->home->action) ? _Controller::$config->home->action : 'index';
}

// if no action is set, or access to hidden methods is attempted, a default "index" action is used.
if (empty($_GET['action']) || $_GET['action'][0] == '_') {
  $_GET['action'] = 'index';
}

// call controller->action; don't worry, this is safe (on error you get 404, light MVC guarantees this).
$controller = $_GET['module'] . '_controller';
$controller = new $controller;
echo $controller->$_GET['action']();
