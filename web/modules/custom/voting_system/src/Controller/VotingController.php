<?php

namespace Drupal\voting_system\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for voting system.
 */
class VotingController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a VotingController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('config.factory')
    );
  }

  /**
   * Displays the voting form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array.
   */
  public function votingForm(Request $request) {
    // Verificar se o sistema está habilitado
    if (!$this->configFactory->get('voting_system.settings')->get('enabled')) {
      return [
        '#theme' => 'voting_no_questions',
        '#message' => $this->t('O sistema de votação está temporariamente indisponível.'),
      ];
    }

    // Buscar todas as perguntas ativas
    $query = $this->entityTypeManager->getStorage('voting_question')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->sort('id', 'DESC');
    $question_ids = $query->execute();

    if (empty($question_ids)) {
      return [
        '#theme' => 'voting_no_questions',
        '#message' => $this->t('Não há perguntas disponíveis para votação no momento.'),
      ];
    }

    $questions = $this->entityTypeManager->getStorage('voting_question')->loadMultiple($question_ids);
    $items = [];

    foreach ($questions as $question) {
      $items[] = [
        '#type' => 'link',
        '#title' => $question->label(),
        '#url' => \Drupal\Core\Url::fromRoute('voting_system.question_vote', ['question_id' => $question->id()]),
      ];
    }

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => $this->t('Perguntas Disponíveis para Votação'),
      '#list_type' => 'ul',
    ];
  }

  /**
   * Displays the voting form for a specific question.
   *
   * @param int $question_id
   *   The question ID.
   *
   * @return array
   *   A render array.
   */
  public function questionVote($question_id) {
    // Verificar se o sistema está habilitado
    if (!$this->configFactory->get('voting_system.settings')->get('enabled')) {
      return [
        '#theme' => 'voting_no_questions',
        '#message' => $this->t('O sistema de votação está temporariamente indisponível.'),
      ];
    }

    $question = $this->entityTypeManager->getStorage('voting_question')->load($question_id);

    if (!$question || !$question->get('status')->value) {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }

    // Buscar opções da pergunta
    $options = $this->entityTypeManager->getStorage('voting_option')
      ->loadByProperties([
        'question_id' => $question_id,
        'status' => 1,
      ]);

    // Ordenar opções por peso
    uasort($options, function ($a, $b) {
      return $a->get('weight')->value <=> $b->get('weight')->value;
    });

    // Construir o formulário
    $form = \Drupal::formBuilder()->getForm('Drupal\voting_system\Form\VotingForm', $question, $options);

    $build = [
      '#theme' => 'voting_form',
      '#question' => $question,
      '#form' => $form,
      '#attached' => [
        'library' => [
          'voting_system/voting-form',
        ],
      ],
      '#cache' => [
        'max-age' => 0,
        'contexts' => ['url.query_args:result'],
      ],
    ];

    return $build;
  }

  /**
   * Verifica se o usuário já votou na pergunta.
   *
   * @param int $question_id
   *   O ID da pergunta.
   *
   * @return bool
   *   TRUE se o usuário já votou, FALSE caso contrário.
   */
  protected function hasUserVoted($question_id) {
    return FALSE;
  }

  /**
   * Página de administração do sistema de votação.
   *
   * @return array
   *   Um array de renderização com a página de administração.
   */
  public function adminPage() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Welcome to the Voting System administration page.'),
    ];
  }

  /**
   * Lista todas as perguntas de votação cadastradas.
   *
   * @return array
   *   Um array de renderização com a lista de perguntas.
   */
  public function questionList() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('List of voting questions will be displayed here.'),
    ];
  }

  /**
   * Exibe a lista de votos registrados no sistema.
   *
   * @return array
   *   Um array de renderização com a lista de votos.
   */
  public function voteList() {
    $list_builder = $this->entityTypeManager->getListBuilder('voting_vote');
    return $list_builder->render();
  }

} 