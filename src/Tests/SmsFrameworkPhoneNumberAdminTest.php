<?php

/**
 * @file
 * Contains \Drupal\sms\Tests\SmsFrameworkPhoneNumberAdminTest.
 */

namespace Drupal\sms\Tests;

use Drupal\Component\Utility\Unicode;
use Drupal\field\FieldStorageConfigInterface;

/**
 * Tests phone number administration user interface.
 *
 * @group SMS Framework
 */
class SmsFrameworkPhoneNumberAdminTest extends SmsFrameworkWebTestBase {

  public static $modules = ['block', 'entity_test'];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->drupalPlaceBlock('page_title_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');

    $account = $this->drupalCreateUser([
      'administer smsframework',
    ]);
    $this->drupalLogin($account);

  }

  /**
   * Tests phone number list
   */
  public function testPhoneNumberList() {
    $this->drupalGet('admin/config/smsframework/phone_number');
    $this->assertRaw(t('No phone number settings found.'));
    $this->assertLinkByHref('admin/config/smsframework/phone_number/add');
  }

  /**
   * CRUD a phone number settings via UI.
   */
  public function testPhoneNumberCrud() {
    // Add a new phone number config.
    $this->drupalGet('admin/config/smsframework/phone_number/add');
    $this->assertResponse(200);

    $edit = [
      'entity_bundle' => 'entity_test|entity_test',
      'field_mapping[phone_number]' => '!create',
    ];
    $this->drupalPostForm('admin/config/smsframework/phone_number/add', $edit, t('Save'));

    $this->assertUrl('admin/config/smsframework/phone_number');
    $t_args = ['%id' => 'entity_test.entity_test'];
    $this->assertRaw(t('Phone number settings %id created.', $t_args));
    $this->assertRaw('<td>entity_test</td>
                      <td>entity_test</td>', 'Phone number settings displayed as row.');
    $this->assertLinkByHref('admin/config/smsframework/phone_number/entity_test.entity_test');
    $this->assertLinkByHref('admin/config/smsframework/phone_number/entity_test.entity_test/delete');

    // Ensure a phone number config cannot have the same bundle as pre-existing.
    $this->drupalGet('admin/config/smsframework/phone_number/add');
    $this->assertNoOption('edit-entity-bundle', 'entity_test|entity_test');

    // Edit phone number settings.
    $this->drupalGet('admin/config/smsframework/phone_number/entity_test.entity_test');
    $this->assertField('field_mapping[phone_number]', 'Phone number field exists.');
    $this->assertNoField('entity_bundle', 'Bundle field does not exist.');
    $this->assertOptionSelected('edit-field-mapping-phone-number', 'phone_number');

    // Ensure edit form is saving correctly.
    $edit = [
      'code_lifetime' => '7777',
    ];
    $this->drupalPostForm('admin/config/smsframework/phone_number/entity_test.entity_test', $edit, t('Save'));
    //
    $this->assertEqual(7777, $this->config('sms.phone.entity_test.entity_test')->get('duration_verification_code_expire'));

    // Delete new phone number settings.
    $this->drupalGet('admin/config/smsframework/phone_number/entity_test.entity_test/delete');
    $this->assertRaw(t('Are you sure you want to delete SMS phone number settings %label?', [
      '%label' => 'entity_test.entity_test',
    ]));
    $this->drupalPostForm('admin/config/smsframework/phone_number/entity_test.entity_test/delete', [], t('Delete'));
    $this->assertUrl('admin/config/smsframework/phone_number');
    $this->assertRaw(t('Phone number settings %label was deleted.', [
      '%label' => 'entity_test.entity_test',
    ]));
    $this->assertRaw('No phone number settings found.');
  }

  /**
   * Test field creation for new phone number settings.
   */
  public function testPhoneNumberFieldCreate() {
    $field_name_telephone = 'phone_number';

    // Test the unique field name generator by creating pre-existing fields.
    $field_storage = $this->entityTypeManager->getStorage('field_storage_config');
    $field_storage->create([
      'entity_type' => 'entity_test',
      'field_name' => $field_name_telephone,
      'type' => 'telephone',
    ])->save();

    $edit = [
      'entity_bundle' => 'entity_test|entity_test',
      'field_mapping[phone_number]' => '!create',
    ];
    $this->drupalPostForm('admin/config/smsframework/phone_number/add', $edit, t('Save'));

    $field_name_telephone .= '_2';
    $field_config = $field_storage->load('entity_test.' . $field_name_telephone);
    $this->assertTrue($field_config instanceof FieldStorageConfigInterface, 'Field config created.');

    // Ensure field name is associated with config.
    $this->drupalGet('admin/config/smsframework/phone_number/entity_test.entity_test');
    $this->assertResponse(200);
    $this->assertOptionSelected('edit-field-mapping-phone-number', $field_name_telephone);
  }

  /**
   * Test using existing fields for new phone number settings.
   */
  public function testPhoneNumberFieldExisting() {
    $field_storage = $this->entityTypeManager->getStorage('field_storage_config');
    $field_instance = $this->entityTypeManager->getStorage('field_config');

    // Create a field so it appears as a pre-existing field.
    /** @var \Drupal\field\FieldStorageConfigInterface $field_telephone */
    $field_telephone = $field_storage->create([
      'entity_type' => 'entity_test',
      'field_name' => Unicode::strtolower($this->randomMachineName()),
      'type' => 'telephone',
    ]);
    $field_telephone->save();

    $field_instance->create([
      'entity_type' => 'entity_test',
      'bundle' => 'entity_test',
      'field_name' => $field_telephone->getName(),
    ])->save();

    $edit = ['entity_bundle' => 'entity_test|entity_test'];
    $this->drupalPostAjaxForm('admin/config/smsframework/phone_number/add', $edit, 'entity_bundle');

    $edit['field_mapping[phone_number]'] = $field_telephone->getName();
    $this->drupalPostForm(NULL, $edit, t('Save'));

    $this->drupalGet('admin/config/smsframework/phone_number/entity_test.entity_test');
    $this->assertResponse(200);
    $this->assertOptionSelected('edit-field-mapping-phone-number', $field_telephone->getName());
  }

}
