<?php

namespace Drupal\voting_system;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Option entities.
 *
 * @ingroup voting_system
 */
class OptionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['question'] = $this->t('Question');
    $header['weight'] = $this->t('Weight');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\voting_system\Entity\Option $entity */
    $row['id'] = $entity->id();
    $row['title'] = Link::createFromRoute(
      $entity->label(),
      'entity.voting_option.edit_form',
      ['voting_option' => $entity->id()]
    );
    $row['question'] = $entity->get('question_id')->entity ? $entity->get('question_id')->entity->label() : '';
    $row['weight'] = $entity->get('weight')->value;
    $row['status'] = $entity->get('status')->value ? $this->t('Published') : $this->t('Unpublished');
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    
    if ($entity->access('view')) {
      $operations['view'] = [
        'title' => $this->t('View'),
        'weight' => 0,
        'url' => $entity->toUrl(),
      ];
    }
    
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('No voting options available.');
    
    // Add the "Add voting option" link.
    $build['add_option'] = [
      '#type' => 'link',
      '#title' => $this->t('Add voting option'),
      '#url' => Url::fromRoute('entity.voting_option.add_form'),
      '#attributes' => [
        'class' => ['button', 'button--primary', 'button--small'],
      ],
      '#weight' => -10,
    ];
    
    return $build;
  }

} 