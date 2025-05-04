<?php

namespace Drupal\voting_system\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Fornece o recurso REST para uma pergunta específica.
 *
 * @RestResource(
 *   id = "voting_question_resource",
 *   label = @Translation("Voting Question Resource"),
 *   uri_paths = {
 *     "canonical" = "/api/voting/questions/{id}"
 *   }
 * )
 */
class VotingQuestionResource extends ResourceBase {

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
   * GET: Retorna uma pergunta específica com suas opções.
   */
  public function get($id) {
    $question = $this->entityTypeManager->getStorage('voting_question')->load($id);

    if (!$question) {
      throw new NotFoundHttpException('Pergunta não encontrada.');
    }

    $data = [
      'id' => $question->id(),
      'title' => $question->label(),
      'show_results' => (bool) $question->get('show_results')->value,
      'status' => (bool) $question->get('status')->value,
      'options' => [],
    ];

    // Carrega as opções da pergunta
    $options = $this->entityTypeManager->getStorage('voting_option')
      ->loadByProperties([
        'question_id' => $question->id(),
        'status' => 1,
      ]);

    foreach ($options as $option) {
      $data['options'][] = [
        'id' => $option->id(),
        'label' => $option->label(),
        'description' => $option->get('description')->value,
        'image' => $option->get('image')->entity ? file_create_url($option->get('image')->entity->getFileUri()) : NULL,
      ];
    }

    return new ResourceResponse($data, 200);
  }
} 