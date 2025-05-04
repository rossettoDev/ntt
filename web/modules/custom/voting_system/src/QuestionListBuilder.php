<?php

namespace Drupal\voting_system;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a list controller for the voting question entity type.
 */
class QuestionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['title'] = $this->t('Title');
    $header['identifier'] = $this->t('Identifier');
    $header['status'] = $this->t('Status');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\voting_system\Entity\Question $entity */
    $row['id'] = $entity->id();
    $row['title'] = Link::createFromRoute(
      $entity->label(),
      'entity.voting_question.edit_form',
      ['voting_question' => $entity->id()]
    );
    $row['identifier'] = $entity->get('identifier')->value;
    $row['status'] = $entity->get('status')->value ? $this->t('Active') : $this->t('Inactive');
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
    $build['table']['#empty'] = $this->t('No voting questions available.');
    
    // Add the "Add voting question" link.
    $build['add_question'] = [
      '#type' => 'link',
      '#title' => $this->t('Add voting question'),
      '#url' => Url::fromRoute('entity.voting_question.add_form'),
      '#attributes' => [
        'class' => ['button', 'button--primary', 'button--small'],
      ],
      '#weight' => -10,
    ];
    
    return $build;
  }

} 