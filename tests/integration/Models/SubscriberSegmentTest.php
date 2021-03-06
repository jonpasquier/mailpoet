<?php
namespace MailPoet\Test\Models;

use MailPoet\Models\Subscriber;
use MailPoet\Models\Segment;
use MailPoet\Models\SubscriberSegment;

class SubscriberSegmentTest extends \MailPoetTest {

  function _before() {
    $this->subscriber = Subscriber::createOrUpdate(array(
      'email' => 'john.doe@mailpoet.com',
      'first_name' => 'John',
      'last_name' => 'Doe',
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));
    $this->segment_1 = Segment::createOrUpdate(array('name' => 'Segment 1'));
    $this->segment_2 = Segment::createOrUpdate(array('name' => 'Segment 2'));

    $this->wp_segment = Segment::getWPSegment();
  }

  function testItCanSubscribeToSegments() {
    $result = SubscriberSegment::subscribeToSegments($this->subscriber, array(
        $this->segment_1->id,
        $this->segment_2->id
    ));
    expect($result)->true();

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(2);
  }

  function testItCanResetSubscriptions() {
    // subscribe to the first segment
    $result = SubscriberSegment::subscribeToSegments($this->subscriber, array(
        $this->segment_1->id
    ));
    expect($result)->true();

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(1);
    expect($subscribed_segments[0]['name'])->equals($this->segment_1->name);

    // reset subscriptions to second segment
    SubscriberSegment::resetSubscriptions($this->subscriber, array(
        $this->segment_2->id
    ));

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(1);
    expect($subscribed_segments[0]['name'])->equals($this->segment_2->name);
  }

  function testItCanUnsubscribeFromSegments() {
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_1->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_2->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));

    // unsubscribe subscriber from first segment
    $result = SubscriberSegment::unsubscribeFromSegments($this->subscriber,
      array(
        $this->segment_1->id
      )
    );
    expect($result)->true();

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(1);
    expect($subscribed_segments[0]['name'])->equals($this->segment_2->name);
  }

  function testItDoesNotUnsubscribeFromWPSegment() {
    $subscriber = $this->subscriber;
    $segment_1 = $this->segment_1;
    $segment_1->type = Segment::TYPE_WP_USERS;
    $segment_1->save();
    $segment_2 = $this->segment_2;
    $subscriber_segment = SubscriberSegment::createOrUpdate(
      array(
        'subscriber_id' => $subscriber->id,
        'segment_id' => $segment_1->id,
        'status' => Subscriber::STATUS_SUBSCRIBED
      )
    );
    $subscriber_segment = SubscriberSegment::createOrUpdate(
      array(
        'subscriber_id' => $subscriber->id,
        'segment_id' => $this->segment_2->id,
        'status' => Subscriber::STATUS_SUBSCRIBED
      )
    );

    // verify that subscriber is subscribed to 2 segments
    $subscriber = Subscriber::findOne($subscriber->id)->withSubscriptions();
    expect($subscriber->subscriptions[0]['status'])->equals(Subscriber::STATUS_SUBSCRIBED);
    expect($subscriber->subscriptions[0]['segment_id'])->equals($segment_1->id);
    expect($subscriber->subscriptions[1]['status'])->equals(Subscriber::STATUS_SUBSCRIBED);
    expect($subscriber->subscriptions[1]['segment_id'])->equals($segment_2->id);

    // verify that subscriber is not subscribed only to the non-WP segment (#2)
    SubscriberSegment::unsubscribeFromSegments($subscriber, array($segment_1->id, $segment_2->id));
    $subscriber = Subscriber::findOne($subscriber->id)->withSubscriptions();

    expect($subscriber->subscriptions[0]['status'])->equals(Subscriber::STATUS_SUBSCRIBED);
    expect($subscriber->subscriptions[0]['segment_id'])->equals($segment_1->id);
    expect($subscriber->subscriptions[1]['status'])->equals(Subscriber::STATUS_UNSUBSCRIBED);
    expect($subscriber->subscriptions[1]['segment_id'])->equals($segment_2->id);
  }

  function testItCanUnsubscribeFromAllSegments() {
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_1->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_2->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));

    // unsubscribe subscriber from all segments
    $result = SubscriberSegment::unsubscribeFromSegments($this->subscriber);
    expect($result)->true();

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->isEmpty();

    // the relations still exist but now have a status of "unsubscribed"
    $subscriptions_count = SubscriberSegment::where(
        'subscriber_id', $this->subscriber->id
      )
      ->where('status', Subscriber::STATUS_UNSUBSCRIBED)
      ->count();
    expect($subscriptions_count)->equals(2);
  }

  function testItCanResubscribeToAllSegments() {
    $result = SubscriberSegment::subscribeToSegments($this->subscriber, array(
        $this->segment_1->id,
        $this->segment_2->id
    ));
    expect($result)->true();

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(2);

    $result = SubscriberSegment::unsubscribeFromSegments($this->subscriber);
    expect($result)->true();

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(0);

    SubscriberSegment::resubscribeToAllSegments($this->subscriber);

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(2);
  }

  function testItCanDeleteSubscriptions() {
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_1->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_2->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));

    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(2);

    // completely remove all subscriptions
    SubscriberSegment::deleteSubscriptions($this->subscriber);

    $subscriptions_count = SubscriberSegment::where(
        'subscriber_id', $this->subscriber->id
      )->count();
    expect($subscriptions_count)->equals(0);
  }

  function testItCanDeleteManySubscriptions() {
    // subscribe first subscriber to segments
    SubscriberSegment::subscribeToSegments($this->subscriber, array(
      $this->segment_1->id, $this->segment_2->id
    ));
    // create a second subscriber
    $subscriber_2 = Subscriber::createOrUpdate(array(
      'email' => 'jane.doe@mailpoet.com',
      'first_name' => 'Jane',
      'last_name' => 'Doe',
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));
    // subscribe her to segments
    SubscriberSegment::subscribeToSegments($subscriber_2, array(
      $this->segment_1->id, $this->segment_2->id
    ));

    expect(SubscriberSegment::count())->equals(4);

    $result = SubscriberSegment::deleteManySubscriptions(array(
      $this->subscriber->id, $subscriber_2->id
    ));
    expect($result)->true();

    expect(SubscriberSegment::count())->equals(0);
  }

  function testItCanCreateOrUpdate() {
    // create relationship between subscriber and a segment
    $result = SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_1->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));
    expect($result->id > 0)->true();
    expect($result->getErrors())->false();

    // check that we have the proper status
    $created = SubscriberSegment::findOne($result->id);
    expect($created->status)->equals(Subscriber::STATUS_SUBSCRIBED);

    // update same combination of subscriber/segment with a different status
    $result = SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_1->id,
      'status' => Subscriber::STATUS_UNSUBSCRIBED
    ));
    expect($result->id > 0)->true();
    expect($result->getErrors())->false();

    // check updated status
    $updated = SubscriberSegment::findOne($created->id);
    expect($updated->status)->equals(Subscriber::STATUS_UNSUBSCRIBED);

    // we should have only one relationship for that user
    $subscriptions_count = SubscriberSegment::where(
      'subscriber_id', $this->subscriber->id
      )
      ->where('segment_id', $this->segment_1->id)
      ->count();
    expect($subscriptions_count)->equals(1);
  }

  function testItCanFilterBySubscribedStatus() {
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_1->id,
      'status' => Subscriber::STATUS_SUBSCRIBED
    ));
    SubscriberSegment::createOrUpdate(array(
      'subscriber_id' => $this->subscriber->id,
      'segment_id' => $this->segment_2->id,
      'status' => Subscriber::STATUS_UNSUBSCRIBED
    ));

    $subscriptions_count = SubscriberSegment::count();
    expect($subscriptions_count)->equals(2);

    $subscriptions_count = SubscriberSegment::filter('subscribed')->count();
    expect($subscriptions_count)->equals(1);
  }

  function testItCannotUnsubscribeFromWPSegment() {
    // subscribe to a segment and the WP segment
    $result = SubscriberSegment::subscribeToSegments($this->subscriber, array(
        $this->segment_1->id,
        $this->wp_segment->id
    ));
    expect($result)->true();

    // unsubscribe from all segments
    $result = SubscriberSegment::unsubscribeFromSegments($this->subscriber);
    expect($result)->true();

    // the subscriber should still be subscribed to the WP segment
    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(1);
    expect($subscribed_segments[0]['name'])->equals($this->wp_segment->name);
  }

  function testItCannotDeleteSubscriptionToWPSegment() {
    // subscribe to a segment and the WP segment
    $result = SubscriberSegment::subscribeToSegments($this->subscriber, array(
        $this->segment_1->id,
        $this->wp_segment->id
    ));
    expect($result)->true();

    // delete all subscriber's subscriptions
    $result = SubscriberSegment::deleteSubscriptions($this->subscriber);
    expect($result)->true();

    // the subscriber should still be subscribed to the WP segment
    $subscribed_segments = $this->subscriber->segments()->findArray();
    expect($subscribed_segments)->count(1);
    expect($subscribed_segments[0]['name'])->equals($this->wp_segment->name);
  }

  function _after() {
    Segment::deleteMany();
    Subscriber::deleteMany();
    SubscriberSegment::deleteMany();
  }
}