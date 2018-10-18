<?php
namespace MailPoet\Util\Notices;

use MailPoet\Models\Setting;
use MailPoet\Util\Helpers;
use MailPoet\WP\Notice as WPNotice;

class DiscountsAnnouncement {

  const OPTION_NAME = 'mailpoet_display_discounts_announcement_q4_2018';

  function enable() {
    Setting::setValue(self::OPTION_NAME, true);
  }

  function disable() {
    Setting::setValue(self::OPTION_NAME, false);
  }

  function init($should_display) {
    $should_display = $should_display && (time() <= strtotime('2018-11-30 23:59:59'));
    if($should_display && Setting::getValue(self::OPTION_NAME, true)) {
      return $this->display();
    }
  }

  private function display() {
    $message = Helpers::replaceLinkTags(
      __('<h3>Save on MailPoet Premium for a limited time. Discounts up to 40%</h3>
          <p>Our annual sale is a good opportunity to get more detailed stats & great email deliverability. Don’t miss out!</p>
        [link]Visit the MailPoet Premium page[/link]', 'mailpoet'),
      'admin.php?page=mailpoet-premium',
      array('target' => '_blank', 'class' => 'button button-primary')
    );
    $message .= '<script>jQuery(function($) {$(document).on("click", ".mailpoet-dismissible-notice .notice-dismiss",function dismiss() {const type = $(this).closest(".mailpoet-dismissible-notice").data("notice");$.ajax(window.ajaxurl,{type: "POST",data: {action: "dismissed_notice_handler",type,}});});});</script>';

    $extra_classes = 'mailpoet-dismissible-notice is-dismissible';
    $data_notice_name = self::OPTION_NAME;

    WPNotice::displaySuccess($message, $extra_classes, $data_notice_name);
    return $message;
  }

}
