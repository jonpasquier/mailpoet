<?php
use MailPoet\Router\Mailer;

class MailerCest {

  function __construct() {
    $this->router = new Mailer();
  }

  function itCanConstruct() {
    expect($this->router->from)->equals('Sender <staff@mailpoet.com>');
  }

  function itCanTransformSubscriber() {
    expect($this->router->transformSubscriber('test@email.com'))
      ->equals('test@email.com');
    expect($this->router->transformSubscriber(
      array(
        'email' => 'test@email.com'
      ))
    )->equals('test@email.com');
    expect($this->router->transformSubscriber(
      array(
        'first_name' => 'First',
        'email' => 'test@email.com'
      ))
    )->equals('First <test@email.com>');
    expect($this->router->transformSubscriber(
      array(
        'last_name' => 'Last',
        'email' => 'test@email.com'
      ))
    )->equals('Last <test@email.com>');
    expect($this->router->transformSubscriber(
      array(
        'first_name' => 'First',
        'last_name' => 'Last',
        'email' => 'test@email.com'
      ))
    )->equals('First Last <test@email.com>');
  }

  function itCanConfigureMailer() {
    $mailer = $this->router->buildMailer();
    $class = 'Mailpoet\\Mailer\\' .
      ((isset($this->router->mailer['type'])) ?
        $this->router->mailer['type'] . '\\' . $this->router->mailer['name'] :
        $this->router->mailer['name']
      );
    expect($mailer instanceof $class)->true();
    expect(method_exists($mailer, 'send'))->true();
  }

  function itCanSend() {
    $newsletter = array(
      'subject' => 'testing Mailer router with ' . $this->router->mailer['name'],
      'body' => array(
        'html' => 'HTML body',
        'text' => 'TEXT body'
      )
    );
    $subscriber = array(
      'first_name' => 'First',
      'last_name' => 'Last',
      'email' => 'mailpoet-phoenix-test@mailinator.com'
    );
    expect($this->router->send($newsletter, $subscriber))->true();
  }
}