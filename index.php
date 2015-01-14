<?php
/**
 * Contela's loader.
 * If you see this in a browser, I should probably shit bricks. Don't hit me with a frying pan.
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

try {
  require_once './core/lightMVC.php';  // lightMVC framework
  require_once './core/config.php';    // config   loads config from file
  require_once './core/presets.php';   // presets  assigning the LightMVC variables based on config
  require_once './core/session.php';   // session  user login management
  require_once './core/lang.php';      // language loads language strings and methods
  require_once './core/router.php';    // router   loads actual code of a module
} catch (Exception $e) {
  if (defined('DEBUG'))
    echo 'Error['.$e->getCode().']: '.$e->getMessage();
  else
    echo 'Critical error. We\'re terribly sorry :(';
}
