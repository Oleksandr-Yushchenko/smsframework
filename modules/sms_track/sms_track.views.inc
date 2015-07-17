<?php
/**
 * @file
 * SMS Framework Message Tracking feature module: views
 *
 * @package sms
 * @subpackage sms_track
 */

/**
 * Implement hook_views_data().
 */
function sms_track_views_data() {
  return array(
    'sms_track' => array(
      'table' => array(
        'group' => t('SMS Archive'),
        'base' => array(
          'field' => 'id',
          'title' => t('SMS Archive'),
          'help' => t("An archive of sent and received SMS messages."),
          'weight' => 0,
        ),
      ),
      'id' => array(
        'title' => t('ID'),
        'help' => t('Message archive ID.'),
        'field' => array(
          'id' => 'standard',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
      ),
      'created' => array(
        'title' => t('Timestamp'),
        'help' => t('Message creation timestamp.'),
        'field' => array(
          'id' => 'date',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'date',
        ),
        'filter' => array(
          'id' => 'date',
        ),
        'sort' => array(
          'id' => 'date',
        ),
      ),
      'dir' => array(
        'title' => t('Direction'),
        'help' => t('Incoming or Outgoing.'),
        'field' => array(
          'id' => 'sms_direction',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
      ),
      'number' => array(
        'title' => t('Remote number'),
        'help' => t('Phone number of remote recipient/sender.'),
        'field' => array(
          'id' => 'standard',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
      ),
      'message' => array(
        'title' => t('Message'),
        'help' => t('The message body text.'),
        'field' => array(
          'id' => 'standard',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
      ),
      'local_number' => array(
        'real field' => 'options',
        'title' => t('Local number'),
        'help' => t('Phone number or ID of remote recipient/sender.'),
        'field' => array(
          'id' => 'sms_local_number',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
      ),
      'gateway' => array(
        'real field' => 'options',
        'title' => t('Gateway'),
        'help' => t('SMS gateway module name.'),
        'field' => array(
          'id' => 'sms_gateway',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
      ),
      // Additional Author and Recipient ID fields.
      'author' => array(
        'title' => t('Author'),
        'help' => t('User ID of remote author.'),
        'field' => array(
          'id' => 'standard',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
        'relationship' => array(
          'title' => t('SMS Author'),
          'help' => t('Relate sms to the user who created it. This relationship will create one record for each sms sent by the user.'),
          'id' => 'standard',
          'base' => 'users_field_data',
          'base field' => 'uid',
          'field' => 'author',
          'label' => t('Author'),
        ),
      ),
      'recipient' => array(
        'title' => t('Recipient'),
        'help' => t('User ID of remote recipient.'),
        'field' => array(
          'id' => 'standard',
          'click sortable' => TRUE,
        ),
        'argument' => array(
          'id' => 'standard',
        ),
        'filter' => array(
          'id' => 'standard',
        ),
        'sort' => array(
          'id' => 'standard',
        ),
        'relationship' => array(
          'title' => t('SMS Recipient'),
          'help' => t('Relate sms to the user who received it. This relationship will create one record for each sms received by the user.'),
          'id' => 'standard',
          'base' => 'users_field_data',
          'base field' => 'uid',
          'field' => 'recipient',
          'label' => t('Recipient'),
        ),
      ),
    ),
  );
}