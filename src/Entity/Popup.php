<?php

namespace Drupal\customize_popup\Entity;

use Drupal\Console\Command\Shared\TranslationTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\link\LinkItemInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Popup entity.
 *
 * @ingroup customize_popup
 *
 * @ContentEntityType(
 *   id = "popup",
 *   label = @Translation("Popup"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\customize_popup\PopupListBuilder",
 *     "views_data" = "Drupal\customize_popup\Entity\PopupViewsData",
 *     "translation" = "Drupal\customize_popup\PopupTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\customize_popup\Form\PopupForm",
 *       "add" = "Drupal\customize_popup\Form\PopupForm",
 *       "edit" = "Drupal\customize_popup\Form\PopupForm",
 *       "delete" = "Drupal\customize_popup\Form\PopupDeleteForm",
 *     },
 *     "access" = "Drupal\customize_popup\PopupAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\customize_popup\PopupHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "popup",
 *   data_table = "popup_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer popup entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/popup/{popup}",
 *     "add-form" = "/admin/structure/popup/add",
 *     "edit-form" = "/admin/structure/popup/{popup}/edit",
 *     "delete-form" = "/admin/structure/popup/{popup}/delete",
 *     "collection" = "/admin/structure/popup",
 *   },
 *   field_ui_base_route = "popup.settings"
 * )
 */
class Popup extends ContentEntityBase implements PopupInterface {

  use EntityChangedTrait;

  /**
   * @var \Drupal\Core\Condition\ConditionManager
   */
  private $conditionManager;

  /**
   * Popup constructor.
   *
   * @param array $values
   * @param $entity_type
   * @param bool $bundle
   * @param array $translations
   */
  public function __construct(array $values, $entity_type, bool $bundle = FALSE, array $translations = []) {
    parent::__construct($values, $entity_type, $bundle, $translations);
  }

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
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
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
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
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
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Popup entity.'))
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);


    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => 0,
      ]);

    $fields['body'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Body'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_format',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'text_format',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['path_pages'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Pages'))
      ->setDisplayOptions('form', [
        'type' => 'textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['css'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Custom CSS'))
      ->setDisplayOptions('form', [
        'type' => 'textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Popup is published.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -6,
      ]);


    return $fields;
  }

  /**
   *
   */
  public function isVisible() {
    if ($this->get('path_pages')->count() == 0) {
      return TRUE;
    }

    /** @var \Drupal\system\Plugin\Condition\RequestPath $condition */
    $condition = \Drupal::service('plugin.manager.condition')->createInstance('request_path');
    $condition->setConfig('pages', $this->get('path_pages')->first()->getString());
    return $condition->execute();
  }

}
