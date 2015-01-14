<?php
/**
 * Contela
 * If you see this in a browser, I should probably shit bricks. Don't hit me with a frying pan.
 * @author Paweł Abramowicz <http://abramowicz.org>
 */

class Language {
// TODO: multilang and loading on demand
  protected $strings = array(
    'login_header' => 'Zaloguj się',
    'username_field' => 'Użytkownik',
    'password_field' => 'Hasło',
    'login_button' => 'Zaloguj się',
    'logout' => 'Wyloguj się',
    'toggle_navigation' => 'Przełącz nawigację',
    'manage_account' => 'Zarządzaj kontem ',
  );

// TODO: changing the language to the user, COOKIE and browser settings
  public function __construct() {
  }

  public function __get($name) {
    return array_key_exists($name, $this->strings) ? $this->strings[$name] : null;
  }
}

_View::$lang = new Language;
