<?php

namespace Drupal\voting_system\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\voting_system\Entity\Question;
use Drupal\voting_system\Entity\Vote;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a voting form.
 */
class VotingForm extends FormBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The question entity.
   *
   * @var \Drupal\voting_system\Entity\Question
   */
  protected $question;

  /**
   * The options for the question.
   *
   * @var array
   */
  protected $options;

  /**
   * Constructs a VotingForm object.
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
  public function getFormId() {
    return 'voting_system_voting_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Question $question = NULL, array $options = []) {
    $this->question = $question;
    $this->options = $options;

    $form['#cache'] = [
      'max-age' => 0,
      'contexts' => ['url.query_args:result'],
    ];

    $form['question'] = [
      '#type' => 'markup',
      '#markup' => '<h2>' . $this->question->label() . '</h2>',
    ];

    // Verificar se o parâmetro result está presente na URL
    $show_results = \Drupal::request()->query->get('result') === 'true';

    // Se show_results estiver ativo, mostrar os resultados primeiro
    if ($show_results && $this->question->get('show_results')->value) {
      $form['results'] = $this->buildResults();
    }

    $form['option_id'] = [
      '#type' => 'radios',
      '#title' => $this->t('Escolha uma opção'),
      '#required' => TRUE,
      '#options' => [],
      '#theme_wrappers' => ['radios'],
      '#attached' => [
        'library' => ['voting_system/voting-form'],
      ],
    ];

    foreach ($this->options as $option) {
      $image = $option->get('image')->entity;
      $image_url = $image ? $image->createFileUrl() : '';
      $description = $option->get('description')->value;

      $form['option_id']['#options'][$option->id()] = $this->buildOptionMarkup($option->label(), $image_url, $description);
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Votar'),
    ];

    return $form;
  }

  /**
   * Builds the markup for an option.
   *
   * @param string $label
   *   The option label.
   * @param string $image_url
   *   The image URL.
   * @param string $description
   *   The option description.
   *
   * @return string
   *   The HTML markup for the option.
   */
  protected function buildOptionMarkup($label, $image_url, $description) {
    $markup = '<div class="voting-option">';
    $markup .= '<input type="radio" name="option_id" value="' . $label . '" class="form-radio">';
    
    if ($image_url) {
      $markup .= '<div class="voting-option-image">';
      $markup .= '<img src="' . $image_url . '" alt="' . $label . '">';
      $markup .= '</div>';
    }

    $markup .= '<div class="voting-option-content">';
    $markup .= '<div class="voting-option-label">' . $label . '</div>';
    
    if ($description) {
      $markup .= '<div class="voting-option-description">' . $description . '</div>';
    }
    
    $markup .= '</div>';
    $markup .= '</div>';

    return $markup;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $option_id = $form_state->getValue('option_id');
    $option = \Drupal::entityTypeManager()->getStorage('voting_option')->load($option_id);

    if (!$option || $option->get('question_id')->target_id != $this->question->id()) {
      $form_state->setErrorByName('option_id', $this->t('A opção selecionada não pertence a esta pergunta.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $option_id = $form_state->getValue('option_id');
    $time = \Drupal::time()->getRequestTime();

    try {
      // Criar o voto
      $vote = Vote::create([
        'question_id' => $this->question->id(),
        'option_id' => $option_id,
        'user_id' => $this->currentUser->isAuthenticated() ? $this->currentUser->id() : 0,
        'ip_address' => \Drupal::request()->getClientIp(),
        'created' => $time,
        'changed' => $time,
      ]);

      $vote->save();
      $this->messenger()->addStatus($this->t('Seu voto foi registrado com sucesso!'));

      // Se a configuração de mostrar resultados estiver ativada
      if ($this->question->get('show_results')->value) {
        // Redirecionar para a mesma página com o parâmetro result=true
        $form_state->setRedirect('voting_system.question_vote', 
          ['question_id' => $this->question->id()],
          ['query' => ['result' => 'true']]
        );
      } else {
        $form_state->setRedirect('<front>');
      }
    }
    catch (\Exception $e) {
      $this->messenger()->addError($this->t('Ocorreu um erro ao registrar seu voto. Por favor, tente novamente.'));
      \Drupal::logger('voting_system')->error('Erro ao salvar voto: @error', ['@error' => $e->getMessage()]);
    }
  }

  /**
   * Builds the results section of the form.
   *
   * @return array
   *   The form array for the results section.
   */
  protected function buildResults() {
    $results = [];
    $total_votes = 0;

    // Contar votos para cada opção
    foreach ($this->options as $option) {
      $query = \Drupal::entityQuery('voting_vote')
        ->condition('question_id', $this->question->id())
        ->condition('option_id', $option->id())
        ->accessCheck(FALSE)
        ->count();
      $votes = $query->execute();
      $results[$option->id()] = $votes;
      $total_votes += $votes;
    }

    // Construir o markup dos resultados
    $markup = '<div class="voting-results">';
    $markup .= '<h3>' . $this->t('Resultados da Votação') . '</h3>';
    $markup .= '<p>' . $this->t('Total de votos: @total', ['@total' => $total_votes]) . '</p>';
    $markup .= '<ul class="voting-results-list">';

    foreach ($this->options as $option) {
      $votes = $results[$option->id()];
      $percentage = $total_votes > 0 ? round(($votes / $total_votes) * 100, 1) : 0;
      
      $markup .= '<li class="voting-result-item">';
      $markup .= '<div class="voting-result-label">' . $option->label() . '</div>';
      $markup .= '<div class="voting-result-bar-container">';
      $markup .= '<div class="voting-result-bar" style="width: ' . $percentage . '%"></div>';
      $markup .= '</div>';
      $markup .= '<div class="voting-result-count">' . $votes . ' ' . $this->t('votos') . ' (' . $percentage . '%)</div>';
      $markup .= '</li>';
    }

    $markup .= '</ul>';
    $markup .= '</div>';

    return [
      '#type' => 'markup',
      '#markup' => $markup,
      '#attached' => [
        'library' => ['voting_system/voting-form'],
      ],
    ];
  }
} 