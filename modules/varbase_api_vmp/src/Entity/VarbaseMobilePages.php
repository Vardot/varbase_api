<?php

namespace Drupal\varbase_api_vmp\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\varbase_api_vmp\VarbaseMobilePagesInterface;
use Drupal\user\UserInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the varbase mobile pages entity.
 *
 * @ingroup varbase_api_vmp
 *
 * @ContentEntityType(
 *   id = "varbase_api_vmp",
 *   label = @Translation("Varbase mobile pages entity"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\varbase_api_vmp\VarbaseMobilePagesListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "add" = "Drupal\Core\Entity\ContentEntityForm",
 *       "edit" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\varbase_api_vmp\Form\VarbaseMobilePagesDeleteForm",
 *     },
 *     "access" = "Drupal\varbase_api_vmp\VarbaseMobilePagesAccessControlHandler",
 *   },
 *   base_table = "varbase_api_vmp",
 *   admin_permission = "administer varbase mobile pages entity",
 *   fieldable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/vmp/{varbase_api_vmp}",
 *     "edit-form" = "/admin/structure/vmp/{varbase_api_vmp}/edit",
 *     "delete-form" = "/admin/structure/vmp/{varbase_api_vmp}/delete",
 *     "collection" = "/admin/structure/vmp/list"
 *   },
 *   field_ui_base_route = "varbase_api_vmp.varbase_api_vmp_settings",
 * )
 */
class VarbaseMobilePages extends ContentEntityBase implements VarbaseMobilePagesInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the varbase mobile pages entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the varbase mobile pages entity.'))
      ->setReadOnly(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('Page title.'))
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -6,
        ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -6,
        ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['blocks'] = BaseFieldDefinition::create('viewsreference')
      ->setLabel(t('Blocks'))
      ->setDescription(t('Blocks.'))
      ->setSetting('target_type', 'view')
      ->setSetting('handler', 'default')
      ->setSetting('plugin_types', [
        'block' => 'block',
        'default' => '0',
        'page' => '0',
        'feed' => '0',
        'entity_browser' => '0',
        'attachment' => '0',
      ])
      ->setTargetEntityTypeId('blocks')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'viewsreference_formatter',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    return $fields;
  }

}
