<?php

namespace Drupal\voting_system\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller para relatórios do sistema de votação.
 */
class VotingReportController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a VotingReportController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Exibe o relatório de votos por pergunta.
   *
   * @return array
   *   Um array de renderização.
   */
  public function report() {
    // Buscar todas as perguntas
    $questions = $this->entityTypeManager->getStorage('voting_question')
      ->loadMultiple();

    $build = [
      '#theme' => 'voting_report',
      '#questions' => [],
      '#attached' => [
        'library' => ['voting_system/voting-report'],
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];

    foreach ($questions as $question) {
      $question_data = [
        'id' => $question->id(),
        'label' => $question->label(),
        'options' => [],
        'total_votes' => 0,
      ];

      // Buscar opções da pergunta
      $options = $this->entityTypeManager->getStorage('voting_option')
        ->loadByProperties([
          'question_id' => $question->id(),
          'status' => 1,
        ]);

      // Ordenar opções por peso
      uasort($options, function ($a, $b) {
        return $a->get('weight')->value <=> $b->get('weight')->value;
      });

      // Contar votos para cada opção
      foreach ($options as $option) {
        $query = $this->entityTypeManager->getStorage('voting_vote')
          ->getQuery()
          ->condition('question_id', $question->id())
          ->condition('option_id', $option->id())
          ->accessCheck(FALSE)
          ->count();
        $votes = $query->execute();

        $question_data['options'][] = [
          'label' => $option->label(),
          'votes' => $votes,
          'percentage' => 0, // Será calculado depois
        ];

        $question_data['total_votes'] += $votes;
      }

      // Calcular porcentagens e garantir exibição de 0 votos
      foreach ($question_data['options'] as $idx => $option) {
        $votes = $option['votes'];
        $percentage = ($question_data['total_votes'] > 0) ? round(($votes / $question_data['total_votes']) * 100, 1) : 0;
        $question_data['options'][$idx]['percentage'] = $percentage;
        $question_data['options'][$idx]['votes'] = $votes; // Garante que sempre exiba o número
      }

      $build['#questions'][] = $question_data;
    }

    return $build;
  }
} 