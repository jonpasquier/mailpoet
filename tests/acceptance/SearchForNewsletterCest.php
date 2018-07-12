<?php
namespace MailPoet\Test\Acceptance;
class SearchForNewsletterCest {
  function searchForStandardNewsletter(\AcceptanceTester $I) {
    $I->wantTo('successfully search for a newsletter');
    $newsletter_title = 'Search Test Newsletter ' . \MailPoet\Util\Security::generateRandomString();
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
    // step 3 - design newsletter (update subject)
    $title_element = '[data-automation-id=\'newsletter_title\']';
    $I->waitForElement($title_element);
    $I->seeInCurrentUrl('mailpoet-newsletter-editor');
    $I->fillField($title_element, $newsletter_title);
    $I->click('Next');
    // step 4 - Choose list and send
    $I->waitForText('Final Step: Last Details');
    $I->seeInCurrentUrl('mailpoet-newsletters#/send/');
    $search_field_element = 'input.select2-search__field';
    $I->fillField($search_field_element, 'WordPress Users');
    $I->pressKey($search_field_element, \WebDriverKeys::ENTER);
    $I->click('Save as draft and close');
	// step 5 - search for email just created
    $I->amOnMailpoetPage('Emails');
	$I->click('#search_input');
	$I->fillField('#search_input', "Search Test");
  }
}