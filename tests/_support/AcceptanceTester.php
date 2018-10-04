<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor {
  use _generated\AcceptanceTesterActions;

  /**
   * Define custom actions here
   */
  public function login() {
    $this->amOnPage('/wp-login.php');
    $this->wait(1);// this needs to be here, Username is not filled properly without this line
    $this->fillField('Username', 'admin');
    $this->fillField('Password', 'password');
    $this->click('Log In');
    $this->waitForText('MailPoet', 10);
  }

  /**
   * Define custom actions here
   */
  public function logOut() {
    $I = $this;
    $I->amOnPage('/wp-login.php?action=logout');
    $I->click('log out');
    $I->wait(1);
  }

  /**
   * Navigate to the specified Mailpoet page in the admin.
   *
   * @param string $page The page to visit e.g. Inbox or Status
   */
  public function amOnMailpoetPage($page) {
    $I = $this;
    $I->amOnPage('/wp-admin');
    $I->waitForText('MailPoet', 10);
    $I->click('MailPoet');
    $I->waitForText($page, 5);
    $I->click($page);
    $I->waitForText($page, 5);
  }

  /**
   * Open email designer sidebar section
   *
   * @param string $section_name
   */
  public function openEmailDesignerSidebarSection($section_name) {
    $I = $this;
    // Click on the section heading with the right name
    $selector = '//*[contains(@class, "mailpoet_sidebar_region")]//h3[text()="'.$section_name.'"]';
    $I->seeElement($selector);
    $I->scrollTo($selector);
    $I->click($selector);

    // Wait for the section to become open, which is when it loses the .closed class
    $I->waitForElement(sprintf('%s/ancestor::*[contains(@class, "mailpoet_sidebar_region") and not(contains(@class, "closed"))]', $selector));
  }
}
