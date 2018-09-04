<?php


namespace Drupal\contexts;

use Drupal\contexts\Service\ContextsServiceInterface;
use Drupal\Core\Extension\ModuleUninstallValidatorInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;

/**
 * Class ContextsUninstallValidator.
 *
 * @package Drupal\contexts\UninstallValidator
 */
class ContextsUninstallValidator implements ModuleUninstallValidatorInterface {

  use StringTranslationTrait;

  /**
   * Contexts service.
   *
   * @var ContextsServiceInterface
   */
  protected $contextsService;

  /**
   * ContextsUninstallValidator constructor.
   *
   * @param ContextsServiceInterface $contextsService
   *   Contexts service interface.
   */
  public function __construct(ContextsServiceInterface $contextsService) {

    $this->contextsService = $contextsService;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($module) {

    $reasons = [];
    if ($module === 'contexts') {
      $helperFieldService = $this->contextsService->getFieldHelper();
      foreach ($helperFieldService->getContentEntityTypeBundleInfo() as $entityTypeId => $bundleInfo) {
        foreach ($bundleInfo as $entityBundle => $bundleData) {
          if ($helperFieldService->getFieldConfig($entityTypeId, $entityBundle)) {
            $reasons[] = $this->t('There is contexts field for the entity type: @entity_type, bundle: @bundle', [
              '@entity_type' => $entityTypeId,
              '@bundle' => $entityBundle,
            ]);
          }
        }
      }
      if ($reasons) {
        $reasons[] = $this->t('Contexts fields can be deleted in @contexts_dashboard', [
          '@contexts_dashboard' => Link::fromTextAndUrl($this->t('contexts dashboard'),
            Url::fromRoute('contexts.dashboard')
          )->toString(),
        ]);
      }
    }

    return $reasons;
  }
}
