<?php

namespace Drupal\voting_system\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Vote edit forms.
 *
 * @ingroup voting_system
 */
class VoteForm extends ContentEntityForm {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a VoteForm object.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Se for um novo voto, preencher automaticamente o usuário e IP
    if ($this->entity->isNew()) {
      $form['user_id']['widget'][0]['target_id']['#default_value'] = $this->currentUser->id();
      $form['ip_address']['widget'][0]['value']['#default_value'] = \Drupal::request()->getClientIp();
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $question_id = $form_state->getValue('question_id')[0]['target_id'];
    $option_id = $form_state->getValue('option_id')[0]['target_id'];

    // Verificar se a opção pertence à pergunta
    $option = \Drupal::entityTypeManager()->getStorage('voting_option')->load($option_id);
    if ($option && $option->get('question_id')->target_id != $question_id) {
      $form_state->setErrorByName('option_id', $this->t('A opção selecionada não pertence a esta pergunta.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = &$this->entity;

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label vote.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label vote.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.voting_vote.canonical', ['voting_vote' => $entity->id()]);
  }

} 