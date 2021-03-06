<?php
namespace MailPoet\Test\API\JSON\v1;

use Helper\WordPressHooks as WPHooksHelper;
use MailPoet\API\JSON\Response as APIResponse;
use MailPoet\API\JSON\v1\Setup;
use MailPoet\Models\Setting;

class SetupTest extends \MailPoetTest {
  function _before() {
    Setting::setValue('signup_confirmation.enabled', false);
  }

  function testItCanReinstall() {
    WPHooksHelper::interceptDoAction();

    $router = new Setup();
    $response = $router->reset();
    expect($response->status)->equals(APIResponse::STATUS_OK);

    $signup_confirmation = Setting::getValue('signup_confirmation.enabled');
    expect($signup_confirmation)->true();

    $hook_name = 'mailpoet_setup_reset';
    expect(WPHooksHelper::isActionDone($hook_name))->true();
  }

  function _after() {
    WPHooksHelper::releaseAllHooks();
    Setting::deleteMany();
  }
}
