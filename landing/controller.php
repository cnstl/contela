<?php
class landing_controller extends _Controller {

  protected $layout;

  function __construct() {
      $this->layout = new _View('layout/landing.phtml');
  }

  function index() {
    return $this->layout;
  }
}
