<?php
namespace MailPoet\Test\Acceptance;
class NewsletterPreviewCest {
  function previewStandardNewsletter(\AcceptanceTester $I) {

    $I->wantTo('Preview standard newsletter in browser from editor');
    $newsletter_title = 'Testing Preview Newsletter ' . \MailPoet\Util\Security::generateRandomString();
    $I->login();
    $I->amOnMailpoetPage('Emails');
    $I->click('[data-automation-id=\'new_email\']');
    // step 1 - select notification type
    $I->seeInCurrentUrl('#/new');
    $I->click('[data-automation-id=\'create_standard\']');
    // step 2 - select template
    $standard_template = '[data-automation-id=\'select_template_0\']';
    $I->waitForElement($standard_template);
    $I->see('Newsletters', ['css' => 'a.current']);
    $I->seeInCurrentUrl('#/template');
    $I->click($standard_template);
    // step 3 - preview in browser
    $title_element = '[data-automation-id=\'newsletter_title\']';
    $I->waitForElement($title_element);
    $I->seeInCurrentUrl('mailpoet-newsletter-editor');
    $I->fillField($title_element, $newsletter_title);

    $I->openEmailDesignerSidebarSection('Preview');

    $I->click('View in browser');
    $I->waitForText('Newsletter Preview');
  }
}
