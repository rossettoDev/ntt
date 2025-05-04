<?php

namespace Drupal\voting_system\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * Defines the voting option entity.
 *
 * @ContentEntityType(
 *   id = "voting_option",
 *   label = @Translation("Voting Option"),
 *   label_collection = @Translation("Voting Options"),
 *   label_singular = @Translation("voting option"),
 *   label_plural = @Translation("voting options"),
 *   label_count = @PluralTranslation(
 *     singular = "@count voting option",
 *     plural = "@count voting options",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\voting_system\OptionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\voting_system\Form\OptionForm",
 *       "edit" = "Drupal\voting_system\Form\OptionForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\voting_system\Entity\OptionAccessControlHandler",
 *   },
 *   base_table = "voting_option",
 *   data_table = "voting_option_field_data",
 *   translatable = TRUE,
 *   admin_permission = "administer voting system",
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "langcode" = "langcode",
 *     "label" = "title",
 *   },
 *   links = {
 *     "collection" = "/admin/content/voting-options",
 *     "add-form" = "/admin/content/voting-options/add",
 *     "canonical" = "/admin/content/voting-options/{voting_option}",
 *     "edit-form" = "/admin/content/voting-options/{voting_option}/edit",
 *     "delete-form" = "/admin/content/voting-options/{voting_option}/delete",
 *   },
 * )
 */
class Option extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['question_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Question'))
      ->setDescription(t('The question this option belongs to.'))
      ->setRequired(TRUE)
      ->setSetting('target_type', 'voting_question')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ]);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the option.'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'max_length' => 255,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => 1,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 1,
      ]);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDescription(t('A detailed description of the option.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'text_default',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 2,
        'settings' => [
          'rows' => 5,
        ],
      ]);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Image'))
      ->setDescription(t('An image for the option.'))
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'image',
        'weight' => 3,
        'settings' => [
          'image_style' => 'medium',
        ],
      ])
      ->setDisplayOptions('form', [
        'type' => 'image_image',
        'weight' => 3,
        'settings' => [
          'preview_image_style' => 'thumbnail',
        ],
      ]);

    $fields['weight'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Weight'))
      ->setDescription(t('The weight of this option in relation to other options.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 4,
      ]);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDescription(t('Whether the option is active.'))
      ->setDefaultValue(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'boolean',
        'weight' => 5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 5,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the option was created.'))
      ->setTranslatable(TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the option was last edited.'))
      ->setTranslatable(TRUE);

    return $fields;
  }

} 