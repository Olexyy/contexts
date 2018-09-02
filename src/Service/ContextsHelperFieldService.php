<?php

namespace Drupal\contexts\Service;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;

/**
 * Class ContextsHelperFieldService.
 *
 * @package Drupal\contexts\Service
 */
class ContextsHelperFieldService implements ContextsHelperFieldServiceInterface {

  use StringTranslationTrait;

  /**
   * Base helper.
   *
   * @var ContextsHelperBaseServiceInterface
   */
  protected $helperBaseService;

  /**
   * ContextsHelperFieldService constructor.
   *
   * @param ContextsHelperBaseServiceInterface $helperBaseService
   *   Base helper.
   */
  public function __construct(ContextsHelperBaseServiceInterface $helperBaseService) {

    $this->helperBaseService = $helperBaseService;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextsItemsCount($entityTypeId, $entityBundle) {

    $bundleKey = $this->helperBaseService->getEntityTypeDefinition($entityTypeId)->getKey('bundle');
    $query = $this->helperBaseService->getEntityTypeQuery($entityTypeId);
    if ($bundleKey && $entityTypeId != $entityBundle) {
      $query->condition($bundleKey, $entityBundle);
    }
    $query->exists(ContextsServiceInterface::FIELD_NAME);
    $query->count();

    return $query->execute();
  }

  /**
   * Adds contexts field to entity by bundle.
   *
   * @param string $entityTypeId
   *   Entity type id.
   *
   * @param string $entityBundle
   *   Entity bundle name.
   *
   * @return bool
   *   Execution result.
   */
  public function addContextsField($entityTypeId, $entityBundle) {

    $fieldStorage = $this->getFieldStorageConfig($entityTypeId);
    $field = $this->getFieldConfig($entityTypeId ,$entityBundle);
    if (!$fieldStorage) {
      $fieldStorage = $this->helperBaseService->getEntityTypeStorage('field_storage_config')->create([
        'field_name' => ContextsServiceInterface::FIELD_NAME,
        'type' => 'entity_reference',
        'entity_type' => $entityTypeId,
        'cardinality' => FieldStorageConfig::CARDINALITY_UNLIMITED,
        'settings' => [
          'target_type' => 'context',
        ],
      ]);
      try {
        $fieldStorage->save();
      } catch (\Exception $exception) {
        $this->helperBaseService->catchException($exception);

        return FALSE;
      }
    }
    if (!$field) {
      /** @var FieldConfigInterface $field */
      $field = $this->helperBaseService->getEntityTypeStorage('field_config')->create([
        'field_storage' => $fieldStorage,
        'bundle' => $entityBundle,
        'label' => $this->t('Contexts'),
        'settings' => [],
      ]);
      $entityFormDisplay = entity_get_form_display($entityTypeId, $entityBundle, 'default');
      $entityFormDisplay->setComponent(ContextsServiceInterface::FIELD_NAME, [
        'type' => 'entity_reference_autocomplete',
      ]);
      try {
        $field->save();
        $entityFormDisplay->save();
      }
      catch (\Exception $exception) {
        $this->helperBaseService->catchException($exception);

        return FALSE;
      }

      return TRUE;
    }

    return FALSE;
  }

  /**
   * Removes contexts field form entity by bundle.
   *
   * @param string $entityTypeId
   *   Entity type id.
   *
   * @param string $entityBundle
   *   Entity bundle name.
   *
   * @return bool
   *   Execution result.
   */
  public function removeContextsField($entityTypeId, $entityBundle) {

    if ($field = $this->getFieldConfig($entityTypeId, $entityBundle)) {
      $fieldStorage = $field->getFieldStorageDefinition();
      if ($fieldStorage && !$fieldStorage->isLocked()) {
        try {
          $field->delete();
        }
        catch (\Exception $exception) {
          $this->helperBaseService->catchException($exception);

          return FALSE;
        }

        return $fieldStorage->getUniqueStorageIdentifier();
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldPurgeBatch($batchSize, $fieldStorageIdentifier) {

    $this->helperBaseService->getModuleHandler()->loadInclude('field', 'inc', 'field.purge');
    field_purge_batch($batchSize, $fieldStorageIdentifier);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfig($entityTypeId, $entityBundle) {

    try {

      return $this->helperBaseService
        ->getEntityTypeStorage('field_config')
        ->load($entityTypeId . '.' . $entityBundle . '.' . ContextsServiceInterface::FIELD_NAME);
    }
    catch(\Exception $exception) {
      $this->helperBaseService->catchException($exception);

      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldStorageConfig($entityTypeId) {

    try {

      return $this->helperBaseService
        ->getEntityTypeStorage('field_storage_config')
        ->load($entityTypeId . '.' . ContextsServiceInterface::FIELD_NAME);
    }
    catch(\Exception $exception) {
      $this->helperBaseService->catchException($exception);

      return NULL;
    }
  }

  /**
   * Get the bundle info of all CONTENT FIELDABLE entity types.
   *
   * @return array
   *   An array of bundle information where the outer array is keyed by entity
   *   type. The next level is keyed by the bundle name. The inner arrays are
   *   associative arrays of bundle information, such as the label for the
   *   bundle.
   */
  public function getContentEntityTypeBundleInfo() {

    $bundleInfo = $this->helperBaseService->getEntityTypeBundleInfo()->getAllBundleInfo();
    $results = [];
    try {
      foreach ($bundleInfo as $entityType => $bundles) {
        if ($this->helperBaseService->getEntityTypeDefinition($entityType)->entityClassImplements(FieldableEntityInterface::class)) {
          if ($this->helperBaseService->getEntityTypeDefinition($entityType)->entityClassImplements(ContentEntityInterface::class)) {
            $results[$entityType] = $bundles;
          }
        }
      }
    }
    catch (\Exception $exception) {
      $this->helperBaseService->catchException($exception);
    }

    return $results;
  }

}
