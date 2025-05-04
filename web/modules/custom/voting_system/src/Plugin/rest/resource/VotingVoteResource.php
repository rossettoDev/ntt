<?php

namespace Drupal\voting_system\Plugin\rest\resource;

use Drupal\rest\ResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Fornece o recurso REST para registrar votos.
 *
 * @RestResource(
 *   id = "voting_vote_resource",
 *   label = @Translation("Voting Vote Resource"),
 *   uri_paths = {
 *     "create" = "/api/voting/vote"
 *   }
 * )
 */
class VotingVoteResource extends ResourceBase {

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
   * POST: Registra um voto.
   */
  public function post($data) {
    if (empty($data['question_id']) || empty($data['option_id'])) {
      throw new BadRequestHttpException('ID da pergunta e ID da opção são obrigatórios.');
    }

    // Verifica se a pergunta existe e está ativa
    $question = $this->entityTypeManager->getStorage('voting_question')->load($data['question_id']);
    if (!$question || !$question->get('status')->value) {
      throw new NotFoundHttpException('Pergunta não encontrada ou inativa.');
    }

    // Verifica se a opção existe e está ativa
    $option = $this->entityTypeManager->getStorage('voting_option')->load($data['option_id']);
    if (!$option || !$option->get('status')->value || $option->get('question_id')->value != $data['question_id']) {
      throw new NotFoundHttpException('Opção não encontrada ou inválida para esta pergunta.');
    }

    // Verifica se o usuário já votou nesta pergunta
    $existing_vote = $this->entityTypeManager->getStorage('voting_vote')
      ->loadByProperties([
        'question_id' => $data['question_id'],
        'user_id' => \Drupal::currentUser()->id(),
      ]);

    if (!empty($existing_vote)) {
      throw new BadRequestHttpException('Você já votou nesta pergunta.');
    }

    // Cria o voto
    $vote = $this->entityTypeManager->getStorage('voting_vote')->create([
      'question_id' => $data['question_id'],
      'option_id' => $data['option_id'],
      'user_id' => \Drupal::currentUser()->id(),
    ]);

    try {
      $vote->save();
      return new ResourceResponse([
        'message' => 'Voto registrado com sucesso.',
      ], 201);
    }
    catch (\Exception $e) {
      throw new BadRequestHttpException('Erro ao registrar voto: ' . $e->getMessage());
    }
  }
} 