<?php

namespace Drupal\contexts\Form;

use Drupal\blockchain\Service\BlockchainServiceInterface;
use Drupal\blockchain\Utils\BlockchainBatchHandler;
use Drupal\contexts\Batch\ContextsBatchHandler;
use Drupal\contexts\Service\ContextsServiceInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContextsDashboardForm.
 *
 * @ingroup blockchain
 */
class ContextsDashboardForm extends FormBase {

  /**
   * Contexts service.
   *
   * @var \Drupal\contexts\Service\ContextsServiceInterface
   */
  protected $contextsService;

  /**
   * BlockchainDashboardForm constructor.
   *
   * @param \Drupal\contexts\Service\ContextsServiceInterface $contextsService
   *   Blockchain service.
   */
  public function __construct(ContextsServiceInterface $contextsService) {

    $this->contextsService = $contextsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('contexts.service')
    );
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {

    return 'contexts_dashboard';
  }

  /**
   * Defines the settings form for Blockchain Block entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $helperFieldService = $this->contextsService->getHelperFieldService();
    $form['context_field_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Context aware content'),
      '#open' => TRUE,
      '#attributes' => ['class' => ['package-listing']],
      '#description' => $this->t('Select bundles to be contexts aware.'),
    ];
    foreach ($helperFieldService->getContentEntityTypeBundleInfo() as $entityTypeId => $bundleInfo) {
      $form['context_field_wrapper'][$entityTypeId . '_wrapper'] = [
        '#type' => 'details',
        '#title' => $entityTypeId,
        '#open' => FALSE,
        '#attributes' => ['class' => ['package-listing']],
      ];
      foreach ($bundleInfo as $entityBundle => $details) {
        $form['context_field_wrapper'][$entityTypeId . '_wrapper'][$entityTypeId . '_' . $entityBundle . '_wrapper'] = [
          '#type' => 'details',
          '#title' => $entityTypeId . ' ' . $entityBundle,
          '#open' => TRUE,
          '#attributes' => ['class' => ['package-listing']],
        ];
        if ($field = $helperFieldService->getFieldConfig($entityTypeId, $entityBundle)) {
          $totalCount = $helperFieldService->getContextsItemsCount($entityTypeId, $entityBundle);
          $form['context_field_wrapper'][$entityTypeId . '_wrapper'][$entityTypeId . '_' . $entityBundle . '_wrapper']['details'] = [
            '#type' => 'item',
            '#title' => $this->t('This bundle is context aware'),
            '#markup' => $totalCount,
            '#description' => $this->t('Context items in storage.'),
          ];
          $form['context_field_wrapper'][$entityTypeId . '_wrapper'][$entityTypeId . '_' . $entityBundle . '_wrapper']['button'] = [
            '#type' => 'button',
            '#executes_submit_callback' => TRUE,
            '#submit' => [[$this, 'callbackHandler']],
            '#value' => $this->t('Remove contexts aware'),
            '#entity_bundle' => $entityBundle,
            '#entity_type_id' => $entityTypeId,
            '#name' => 'remove_'.$entityTypeId. '_' .$entityBundle,
            '#total_count' => $totalCount,
          ];
        }
        else {
          $form['context_field_wrapper'][$entityTypeId . '_wrapper'][$entityTypeId . '_' . $entityBundle . '_wrapper']['button'] = [
            '#type' => 'button',
            '#executes_submit_callback' => TRUE,
            '#submit' => [[$this, 'callbackHandler']],
            '#value' => $this->t('Make contexts aware'),
            '#entity_bundle' => $entityBundle,
            '#entity_type_id' => $entityTypeId,
            '#name' => 'add_'.$entityTypeId. '_' .$entityBundle,
          ];
        }
      }
    }

    return $form;
  }

  /**
   * Callback for custom actions.
   *
   * {@inheritdoc}
   */
  public function callbackHandler(array &$form, FormStateInterface $form_state) {

    $helperFieldService = $this->contextsService->getHelperFieldService();
    $this->getRequest()->query->remove('destination');
    $action = $form_state->getTriggeringElement()['#name'];
    $entityTypeId = $form_state->getTriggeringElement()['#entity_type_id'];
    $entityBundle = $form_state->getTriggeringElement()['#entity_bundle'];
    if ($action && $entityTypeId && $entityBundle) {
      if (strpos($action, 'add_') === 0) {
        if ($helperFieldService->addContextsField($entityTypeId, $entityBundle)) {
          $this->messenger()->addStatus($this->t('Context field was set to @entityTypeId @entityBundle', [
            '@entityTypeId' => $entityTypeId,
            '@entityBundle' => $entityBundle,
          ]));
        }
        else {
          $this->messenger()->addError($this->t('Context field was not set to @entityTypeId @entityBundle', [
            '@entityTypeId' => $entityTypeId,
            '@entityBundle' => $entityBundle,
          ]));
        }
      }
      elseif (strpos($action, 'remove_') === 0 ) {
        $totalCount = $form_state->getTriggeringElement()['#total_count'];
        if ($storageId = $helperFieldService->removeContextsField($entityTypeId, $entityBundle)) {
          ContextsBatchHandler::set(ContextsBatchHandler::getFieldPurgeBatchDefinition($storageId, $totalCount));
        }
      }
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
