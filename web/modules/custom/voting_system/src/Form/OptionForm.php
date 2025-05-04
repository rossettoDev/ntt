<?php

namespace Drupal\voting_system\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the voting option entity edit forms.
 */
class OptionForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  protected function prepareEntity() {
    parent::prepareEntity();
    
    $time = \Drupal::time()->getRequestTime();
    
    if ($this->entity->isNew()) {
      $this->entity->set('created', $time);
    }
    
    $this->entity->set('changed', $time);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('Created the %label voting option.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved the %label voting option.', [
          '%label' => $entity->label(),
        ]));
    }

    $form_state->setRedirect('entity.voting_option.canonical', ['voting_option' => $entity->id()]);
  }

} 