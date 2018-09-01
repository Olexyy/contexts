<?php

namespace Drupal\contexts\Service;


use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Class ContextsService.
 *
 * @package Drupal\contexts\Service
 */
class ContextsService implements ContextsServiceInterface {

  use StringTranslationTrait;
  use LoggerChannelTrait;

  /**
   * Entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Module handler.
   *
   * @var ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Entity bundle info.
   *
   * @var EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * {@inheritdoc}
   */
  public static function instance() {

    return \Drupal::service('contexts.service');
  }

  /**
   * ContextsService constructor.
   *
   * @param EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   Entity bundle info.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              ModuleHandlerInterface $moduleHandler,
                              EntityTypeBundleInfoInterface $entityTypeBundleInfo) {

    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
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
      $fieldStorage = $this->getEntityTypeStorage('field_storage_config')->create([
        'field_name' => static::FIELD_NAME,
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
        $this->catchException($exception);

        return FALSE;
      }
    }
    if (!$field) {
      $field = $this->getEntityTypeStorage('field_config')->create([
        'field_storage' => $fieldStorage,
        'bundle' => $entityBundle,
        'label' => $this->t('Contexts'),
        'settings' => [],
      ]);
      try {
        $field->save();
      }
      catch (\Exception $exception) {
        $this->catchException($exception);

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
          $this->catchException($exception);

          return FALSE;
        }

        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Wrapper for field purge batch handler.
   *
   * @param int $batchSize
   *   Size of batch.
   * @param string $fieldStorageIdentifier
   *   Field storage identifier.
   */
  public function fieldPurgeBatch($batchSize, $fieldStorageIdentifier) {

    $this->moduleHandler->loadInclude('field', 'inc', 'field.purge');
    field_purge_batch($batchSize, $fieldStorageIdentifier);
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldConfig($entityTypeId, $entityBundle) {

    try {

      return $this->entityTypeManager
        ->getStorage('field_config')
        ->load($entityTypeId . '.' . $entityBundle . '.' . static::FIELD_NAME);
    }
    catch(\Exception $exception) {
      $this->catchException($exception);

      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldStorageConfig($entityTypeId) {

    try {

      return $this->entityTypeManager
        ->getStorage('field_storage_config')
        ->load($entityTypeId . '.' . static::FIELD_NAME);
    }
    catch(\Exception $exception) {
      $this->catchException($exception);

      return NULL;
    }
  }

  /**
   * Getter for entity storage.
   *
   * @param string $entityType
   *   Entity type.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface|null
   *   Entity storage if any.
   */
  public function getEntityTypeStorage($entityType) {

    try {

      return $this->entityTypeManager
        ->getStorage($entityType);
    }
    catch(\Exception $exception) {
      $this->catchException($exception);

      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeDefinitions() {

    try {

      return $this->entityTypeManager
        ->getDefinitions();
    }
    catch(\Exception $exception) {
      $this->catchException($exception);

      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function catchException(\Exception $exception) {

    $this->getLogger('contexts')
      ->error($exception->getMessage());
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

    $bundleInfo = $this->entityTypeBundleInfo->getAllBundleInfo();
    $results = [];
    foreach ($bundleInfo as $entityType => $bundles) {
      if ($this->entityTypeManager->getDefinition($entityType)->entityClassImplements(FieldableEntityInterface::class)) {
        if ($this->entityTypeManager->getDefinition($entityType)->entityClassImplements(ContentEntityInterface::class)) {
          $results[$entityType] = $bundles;
        }
      }
    }

    return $results;
  }

}
