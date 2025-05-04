<?php

namespace Drupal\voting_system\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block with a list of active voting questions.
 *
 * @Block(
 *   id = "voting_questions_block",
 *   admin_label = @Translation("Questões de Votação"),
 *   category = @Translation("Sistema de Votação")
 * )
 */
class VotingQuestionsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new VotingQuestionsBlock instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account, $return_as_object = FALSE) {
    return AccessResult::allowedIfHasPermission($account, 'access voting questions block');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Verificar se o sistema está habilitado
    if (!$this->configFactory->get('voting_system.settings')->get('enabled')) {
      return [
        '#theme' => 'voting_no_questions',
        '#message' => $this->t('O sistema de votação está temporariamente indisponível.'),
      ];
    }

    // Buscar as 5 perguntas ativas mais recentes
    $query = $this->entityTypeManager->getStorage('voting_question')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('status', 1)
      ->sort('id', 'DESC')
      ->pager(5);
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

    // Adicionar o link "mais..."
    $items[] = [
      '#type' => 'link',
      '#title' => $this->t('mais...'),
      '#url' => \Drupal\Core\Url::fromRoute('voting_system.voting_form'),
      '#attributes' => [
        'class' => ['more-link'],
      ],
    ];

    return [
      '#theme' => 'item_list',
      '#items' => $items,
      '#title' => $this->t('Votação'),
      '#list_type' => 'ul',
      '#cache' => [
        'max-age' => 0, // Desabilitar cache para sempre mostrar as questões mais recentes
      ],
    ];
  }

} 