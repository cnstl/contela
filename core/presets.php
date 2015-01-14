<?php
/**
 * Contela
 * If you see this in a browser, I should probably shit bricks. Don't hit me with a frying pan.
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

// styles directory
_View::$dir = !empty(_Controller::$config->style->dir) ? _Controller::$config->style->dir : './style/';

// database connection and credentials
_Model::$dsn = _Controller::$config->db->dsn;
_Model::$user     = !empty(_Controller::$config->db->user)     ? _Controller::$config->db->user     : null;
_Model::$password = !empty(_Controller::$config->db->password) ? _Controller::$config->db->password : null;
