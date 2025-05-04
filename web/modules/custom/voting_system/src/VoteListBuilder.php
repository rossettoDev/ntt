<?php

namespace Drupal\voting_system;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Vote entities.
 *
 * @ingroup voting_system
 */
class VoteListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('ID');
    $header['question'] = $this->t('Question');
    $header['option'] = $this->t('Option');
    $header['user'] = $this->t('User');
    $header['ip_address'] = $this->t('IP Address');
    $header['created'] = $this->t('Created');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var \Drupal\voting_system\Entity\Vote $entity */
    $row['id'] = $entity->id();
    $row['question'] = $entity->get('question_id')->entity ? $entity->get('question_id')->entity->label() : '';
    $row['option'] = $entity->get('option_id')->entity ? $entity->get('option_id')->entity->label() : '';
    $row['user'] = $entity->get('user_id')->entity ? $entity->get('user_id')->entity->label() : '';
    $row['ip_address'] = $entity->get('ip_address')->value;
    $row['created'] = \Drupal::service('date.formatter')->format($entity->get('created')->value);
    return $row + parent::buildRow($entity);
  }

} 