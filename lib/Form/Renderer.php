<?php
namespace MailPoet\Form;
use MailPoet\Form\Block;
use MailPoet\Form\Util;

if(!defined('ABSPATH')) exit;

class Renderer {
  // public: rendering method
  static function render($form = array()) {
    $html = static::renderStyles($form);
    $html .= static::renderHTML($form);
    return $html;
  }

  static function renderStyles($form = array()) {
    $html = '<style type="text/css">';
    $html .= static::getStyles($form);
    $html .= '</style>';

    return $html;
  }

  static function renderHTML($form = array()) {
    if(isset($form['body']) && !empty($form['body'])) {
      return static::renderBlocks($form['body']);
    }
    return '';
  }

  static function getStyles($form = array()) {
    if(isset($form['styles'])
    && strlen(trim($form['styles'])) > 0) {
      return strip_tags($form['styles']);
    } else {
      return Util\Styles::$defaults;
    }
  }

  // private: rendering methods
  private static function renderBlocks($blocks = array()) {
    $html = '';
    foreach ($blocks as $key => $block) {
      $html .= static::renderBlock($block)."\n";
    }

    return $html;
  }

  private static function renderBlock($block = array()) {
    $html = '';
    switch ($block['type']) {
      case 'html':
        $html .= Block\Html::render($block);
      break;

      case 'divider':
        $html .= Block\Divider::render();
      break;

      case 'checkbox':
        $html .= Block\Checkbox::render($block);
      break;

      case 'radio':
        $html .= Block\Radio::render($block);
      break;

      case 'segment':
        $html .= Block\Segment::render($block);
      break;

      case 'date':
        $html .= Block\Date::render($block);
      break;

      case 'select':
        $html .= Block\Select::render($block);
      break;

      case 'input':
        $html .= Block\Input::render($block);
      break;

      case 'textarea':
        $html .= Block\Textarea::render($block);
      break;

      case 'submit':
        $html .= Block\Submit::render($block);
      break;
    }
    return $html;
  }
}