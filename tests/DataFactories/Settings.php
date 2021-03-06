<?php

namespace MailPoet\Test\DataFactories;

use MailPoet\Models\Setting;

class Settings {

  function withConfirmationEmailSubject($subject = null) {
    if($subject === null) {
      $subject = sprintf('Confirm your subscription to %1$s', get_option('blogname'));
    }
    Setting::setValue('signup_confirmation.subject', $subject);
    return $this;
  }

  function withConfirmationEmailBody($body = null) {
    if($body === null) {
      $body = "Hello,\n\nWelcome to our newsletter!\n\nPlease confirm your subscription to the list(s): [lists_to_confirm] by clicking the link below: \n\n[activation_link]Click here to confirm your subscription.[/activation_link]\n\nThank you,\n\nThe Team";
    }
    Setting::setValue('signup_confirmation.body', $body);
    return $this;
  }

  function withConfirmationEmailEnabled() {
    Setting::setValue('signup_confirmation.enabled', '1');
    return $this;
  }

  function withConfirmationEmailFrom($name, $address) {
    Setting::setValue('signup_confirmation.from', ['name' => $name, 'address' => $address]);
    return $this;
  }

  function withConfirmationEmailDisabled() {
    Setting::setValue('signup_confirmation.enabled', '');
    return $this;
  }

  function withTrackingDisabled() {
    Setting::setValue('tracking.enabled', false);
    return $this;
  }

  function withSkippedTutorials() {
    Setting::setValue('show_intro', 0);
    Setting::setValue('display_nps_poll', 0);
    Setting::setValue('user_seen_editor_tutorial1', 1);
    Setting::setValue('show_congratulate_after_first_newsletter', 0);
    return $this;
  }
}
