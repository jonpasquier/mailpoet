<?php
namespace MailPoet\Models;

if(!defined('ABSPATH')) exit;

class NewsletterOption extends Model {
  public static $_table = MP_NEWSLETTER_OPTION_TABLE;

  function __construct() {
    parent::__construct();
  }
}