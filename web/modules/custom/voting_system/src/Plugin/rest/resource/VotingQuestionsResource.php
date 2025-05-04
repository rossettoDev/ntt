<?php

namespace Drupal\voting_system\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Fornece o recurso REST para perguntas de votação.
 *
 * @RestResource(
 *   id = "voting_questions_resource",
 *   label = @Translation("Voting Questions Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/voting/questions",
 *     "create" = "/api/voting/questions"
 *   }
 * )
 */
class VotingQuestionsResource extends ResourceBase {

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
   * GET: Retorna todas as perguntas.
   */
  public function get() {
    $questions = $this->entityTypeManager->getStorage('voting_question')->loadMultiple();
    $data = [];

    foreach ($questions as $question) {
      $data[] = [
        'id' => $question->id(),
        'title' => $question->label(),
        'show_results' => (bool) $question->get('show_results')->value,
        'status' => (bool) $question->get('status')->value,
      ];
    }

    return new ResourceResponse($data, 200);
  }

  /**
   * POST: Cria uma nova pergunta.
   */
  public function post($data) {
    if (empty($data['title'])) {
      throw new BadRequestHttpException('O título da pergunta é obrigatório.');
    }

    $question = $this->entityTypeManager->getStorage('voting_question')->create([
      'title' => $data['title'],
      'show_results' => $data['show_results'] ?? TRUE,
      'status' => $data['status'] ?? TRUE,
    ]);

    try {
      $question->save();
      return new ResourceResponse([
        'id' => $question->id(),
        'message' => 'Pergunta criada com sucesso.',
      ], 201);
    }
    catch (\Exception $e) {
      throw new BadRequestHttpException('Erro ao criar pergunta: ' . $e->getMessage());
    }
  }
} 