<?php

namespace Drupal\voting_system\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Fornece o recurso REST para resultados de votação.
 *
 * @RestResource(
 *   id = "voting_results_resource",
 *   label = @Translation("Voting Results Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/voting/results/{id}"
 *   }
 * )
 */
class VotingResultsResource extends ResourceBase {

  /**
   * O gerenciador de entidades.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    array $authentication,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $authentication);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->getParameter('rest.authentication'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * GET: Retorna os resultados de uma votação.
   */
  public function get($id) {
    $question = $this->entityTypeManager->getStorage('voting_question')->load($id);

    if (!$question) {
      throw new NotFoundHttpException('Pergunta não encontrada.');
    }

    // Verifica se o usuário tem permissão para ver os resultados
    if (!$question->get('show_results')->value && !\Drupal::currentUser()->hasPermission('administer voting system')) {
      throw new AccessDeniedHttpException('Você não tem permissão para ver os resultados desta votação.');
    }

    $data = [
      'id' => $question->id(),
      'title' => $question->label(),
      'total_votes' => 0,
      'options' => [],
    ];

    // Carrega as opções da pergunta
    $options = $this->entityTypeManager->getStorage('voting_option')
      ->loadByProperties([
        'question_id' => $question->id(),
        'status' => 1,
      ]);

    foreach ($options as $option) {
      $query = $this->entityTypeManager->getStorage('voting_vote')
        ->getQuery()
        ->condition('question_id', $question->id())
        ->condition('option_id', $option->id())
        ->accessCheck(FALSE)
        ->count();
      $votes = $query->execute();

      $data['options'][] = [
        'id' => $option->id(),
        'label' => $option->label(),
        'description' => $option->get('description')->value,
        'image' => $option->get('image')->entity ? file_create_url($option->get('image')->entity->getFileUri()) : NULL,
        'votes' => $votes,
      ];

      $data['total_votes'] += $votes;
    }

    // Calcula as porcentagens
    if ($data['total_votes'] > 0) {
      foreach ($data['options'] as &$option) {
        $option['percentage'] = round(($option['votes'] / $data['total_votes']) * 100, 2);
      }
    }

    return new ResourceResponse($data, 200);
  }
} 