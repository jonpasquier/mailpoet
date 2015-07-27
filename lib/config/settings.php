<?php
namespace MailPoet\Config;
use \MailPoet\WP;

class Settings {

  function __construct() {
    $this->options = new WP\Option();
  }

  function load($name) {
    return $this->options->get($name);
  }

  function save($name, $value) {
    return $this->options->set($name, $value);
  }
}