<?php
namespace MailPoet\Test\Acceptance;
class ConfirmNotificationAutosaveCest {
  function confirmNotificationAutosave(\AcceptanceTester $I) {
    $I->wantTo('Confirm post notifications autosave correctly');
    $newsletter_title = 'Post Notification Autosave Test ' . \MailPoet\Util\Security::generateRandomString();
    $I->login();
    $I->amOnMailpoetPage('Emails');
    $I->click('[data-automation-id=\'new_email\']');
    // step 1 - select notification type
    $I->seeInCurrentUrl('#/new');
    $I->click('[data-automation-id=\'create_notification\']');
    // step 2 - configure schedule
    $I->waitForText('Latest Post Notifications');
    $I->seeInCurrentUrl('#/new/notification');
    $I->selectOption('select[name=intervalType]', 'immediately');
    $I->click('Next');
    // step 3 - select template
    $post_notification_template = '[data-automation-id=\'select_template_0\']';
    $I->waitForElement($post_notification_template);
    $I->see('Post Notifications', ['css' => 'a.current']);
    $I->seeInCurrentUrl('#/template');
    $I->click($post_notification_template);
    // step 4 - design newsletter/confirm autosave (update subject)
    $title_element = '[data-automation-id=\'newsletter_title\']';
    $I->waitForElement($title_element);
    $I->seeInCurrentUrl('mailpoet-newsletter-editor');
    $I->fillField($title_element, $newsletter_title);
    $I->waitForText('autosaved');
  }

}
