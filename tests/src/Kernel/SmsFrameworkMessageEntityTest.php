<?php

/**
 * @file
 * Contains \Drupal\Tests\sms\Kernel\SmsFrameworkMessageEntityTest.
 */

namespace Drupal\Tests\sms\Kernel;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\sms\Message\SmsMessage as StandardSmsMessage;
use Drupal\sms\Entity\SmsMessage;
use Drupal\sms\Tests\SmsFrameworkMessageTestTrait;
use Drupal\sms\Tests\SmsFrameworkTestTrait;
use Drupal\user\Entity\User;
use Drupal\sms\Entity\SmsMessageInterface;

/**
 * Tests SMS message entity.
 *
 * @group SMS Framework
 * @coversDefaultClass \Drupal\sms\Entity\SmsMessage
 */
class SmsFrameworkMessageEntityTest extends SmsFrameworkKernelBase {

  use SmsFrameworkTestTrait;
  use SmsFrameworkMessageTestTrait {
    // Remove 'test' prefix so it will not be run by test runner, rename so we
    // can override.
    testUid as originalUid;
  }

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['sms', 'sms_test_gateway', 'telephone', 'dynamic_entity_reference', 'user', 'entity_test'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('sms');
    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test');
  }

  /**
   * Create a SMS message object for testing.
   *
   * @param array $values
   *   An mixed array of values to pass when creating the SMS message entity.
   *
   * @return \Drupal\sms\Entity\SmsMessageInterface
   */
  protected function createSmsMessage(array $values = []) {
    return SmsMessage::create($values);
  }

  /**
   * Tests validation violation when message is empty.
   */
  public function testMessageEmpty() {
    $sms_message = $this->createSmsMessage();
    $this->assertTrue(in_array('message', $sms_message->validate()->getFieldNames()));
  }

  /**
   * @inheritdoc
   */
  public function testUid() {
    // User must exist or setUid will throw an exception.
    User::create(['uid' => 22, 'name' => 'user'])
      ->save();
    $this->originalUid();
  }

  /**
   * Test sender name is correct when sender name or sender entity is set.
   */
  public function testSenderNameWithSenderEntity() {
    $sender_name = $this->randomMachineName();
    $sender = EntityTest::create()
      ->setName($this->randomMachineName());
    $sender->save();

    $sms_message1 = $this->createSmsMessage();
    $sms_message1->setSender($sender_name);
    $sms_message1->setSenderEntity($sender);
    $this->assertEquals($sender_name, $sms_message1->getSender());

    $sms_message2 = $this->createSmsMessage();
    $sms_message2->setSenderEntity($sender);
    $this->assertEquals($sender->label(), $sms_message2->getSender());
  }

  /**
   * Tests direction of SMS messages.
   *
   * @covers ::getDirection
   * @covers ::setDirection
   */
  public function testDirection() {
    // Check for validation violation for missing direction.
    $sms_message1 = $this->createSmsMessage();
    $this->assertTrue(in_array('direction', $sms_message1->validate()->getFieldNames()));

    $sms_message2 = $this->createSmsMessage()
      ->setDirection(SmsMessageInterface::DIRECTION_OUTGOING);
    $this->assertEquals(SmsMessageInterface::DIRECTION_OUTGOING, $sms_message2->getDirection());

    $sms_message3 = $this->createSmsMessage()
      ->setDirection(SmsMessageInterface::DIRECTION_INCOMING);
    $this->assertEquals(SmsMessageInterface::DIRECTION_INCOMING, $sms_message3->getDirection());
  }

  /**
   * Tests gateway plugin of SMS messages.
   *
   * @covers ::getGateway
   * @covers ::setGateway
   */
  public function testGateway() {
    // Check for validation violation for missing gateway.
    $sms_message1 = $this->createSmsMessage();
    $this->assertTrue(in_array('gateway', $sms_message1->validate()->getFieldNames()));

    $gateway = $this->createMemoryGateway();
    $sms_message2 = $this->createSmsMessage();
    $sms_message2->setGateway($gateway);
    $this->assertEquals($gateway, $sms_message2->getGateway());
  }

  /**
   * Tests sender phone number.
   *
   * @covers ::getSenderNumber
   * @covers ::setSenderNumber
   */
  public function testSenderNumber() {
    $number = '1234567890';
    $sms_message = $this->createSmsMessage();
    $sms_message->setSenderNumber($number);
    $this->assertEquals($number, $sms_message->getSenderNumber());
  }

  /**
   * Tests sender entity.
   *
   * @covers ::getSenderEntity
   * @covers ::setSenderEntity
   */
  public function testSenderEntity() {
    $sms_message1 = $this->createSmsMessage();
    $this->assertEquals(NULL, $sms_message1->getSenderEntity());

    $sender = EntityTest::create();
    $sender->save();
    $sms_message2 = $this->createSmsMessage();
    $sms_message2->setSenderEntity($sender);
    $this->assertEquals($sender->id(), $sms_message2->getSenderEntity()->id());
  }

  /**
   * Tests recipient entity.
   *
   * @covers ::getRecipientEntity
   * @covers ::setRecipientEntity
   */
  public function testRecipientEntity() {
    $sms_message1 = $this->createSmsMessage();
    $this->assertEquals(NULL, $sms_message1->getRecipientEntity());

    $sender = EntityTest::create();
    $sender->save();
    $sms_message2 = $this->createSmsMessage();
    $sms_message2->setRecipientEntity($sender);
    $this->assertEquals($sender->id(), $sms_message2->getRecipientEntity()->id());
  }

  /**
   * Tests is queued.
   *
   * @covers ::isQueued
   * @covers ::setQueued
   */
  public function testQueued() {
    $sms_message1 = $this->createSmsMessage();
    $this->assertFalse($sms_message1->isQueued());

    $sms_message2 = $this->createSmsMessage();
    $sms_message2->setQueued(TRUE);
    $this->assertTrue($sms_message2->isQueued());
  }

  /**
   * Tests created time.
   *
   * @covers ::getCreatedTime
   */
  public function testCreatedTime() {
    $sms_message = $this->createSmsMessage();
    $this->assertEquals(REQUEST_TIME, $sms_message->getCreatedTime());
  }

  /**
   * Tests queue send time.
   *
   * @covers ::getSendTime
   * @covers ::setSendTime
   */
  public function testSendTime() {
    $sms_message1 = $this->createSmsMessage();
    $this->assertEquals(REQUEST_TIME, $sms_message1->getSendTime());

    $time = (new DrupalDateTime('+7 days'))->format('U');
    $sms_message2 = $this->createSmsMessage();
    $sms_message2->setSendTime($time);
    $this->assertEquals($time, $sms_message2->getSendTime());
  }

  /**
   * Tests processed time.
   *
   * @covers ::getProcessedTime
   * @covers ::setProcessedTime
   */
  public function testProcessedTime() {
    $sms_message1 = $this->createSmsMessage();
    $this->assertEquals(NULL, $sms_message1->getProcessedTime());

    $time = (new DrupalDateTime('+7 days'))->format('U');
    $sms_message2 = $this->createSmsMessage();
    $sms_message2->setProcessedTime($time);
    $this->assertEquals($time, $sms_message2->getProcessedTime());
  }

  /**
   * Tests chunked SMS messages are unsaved entities.
   *
   * @covers ::chunkByRecipients
   */
  public function testChunkByRecipientsEntity() {
    $sms_message = $this->createSmsMessage();
    $sms_message->addRecipients(['100', '200']);
    $sms_messages = $sms_message->chunkByRecipients(1);
    $this->assertTrue($sms_messages[0]->isNew());
    $this->assertTrue($sms_messages[1]->isNew());
  }

  /**
   * Ensure data from standard SMS message are passed to SMS message entity.
   */
  public function testConvertToEntityFromStandardSmsMessage() {
    // Need ID otherwise we have to install system module and 'sequences' table.
    $user = User::create(['uid' => 1, 'name' => 'user']);
    $user->save();

    $original = new StandardSmsMessage('', [], '', [], NULL);
    $original
      ->setAutomated(TRUE)
      ->setSender($this->randomMachineName())
      ->addRecipients(['123123123', '456456456'])
      ->setMessage($this->randomMachineName())
      ->setUid($user->id())
      ->setOption('foo', $this->randomMachineName())
      ->setOption('bar', $this->randomMachineName());

    $sms_message = SmsMessage::convertFromSmsMessage($original);

    $this->assertEquals($original->isAutomated(), $sms_message->isAutomated());
    $this->assertEquals($original->getSender(), $sms_message->getSender());
    $this->assertEquals($original->getRecipients(), $sms_message->getRecipients());
    $this->assertEquals($original->getMessage(), $sms_message->getMessage());
    $this->assertEquals($user->id(), $sms_message->getSenderEntity()->id());
    $this->assertEquals($original->getOption('foo'), $sms_message->getOption('foo'));
    $this->assertEquals($original->getOption('bar'), $sms_message->getOption('bar'));
  }

  /**
   * Ensure there is no data loss if an entity is passed to the converter.
   */
  public function testConvertToEntityFromEntitySmsMessage() {
    $recipient = EntityTest::create()
      ->setName($this->randomMachineName());
    $recipient->save();

    $original = SmsMessage::create();
    $original->setMessage($this->randomMachineName());
    // Use a method not common with standard SMS message class.
    $original->setRecipientEntity($recipient);

    $sms_message = SmsMessage::convertFromSmsMessage($original);
    $this->assertEquals($original->getMessage(), $sms_message->getMessage());
    $this->assertEquals($original->getRecipientEntity(), $sms_message->getRecipientEntity());
  }

}