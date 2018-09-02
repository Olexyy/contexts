<?php

namespace Drupal\contexts\Service;

/**
 * Interface ContextsHelperFieldServiceInterface.
 *
 * @package Drupal\contexts\Service
 */
interface ContextsHelperFieldServiceInterface {

  /**
   * Context items count.
   *
   * @param string $entityTypeId
   *   Entity type id.
   * @param string $entityBundle
   *   Entity bundle.
   *
   * @return int
   *   Count.
   */
  public function getContextsItemsCount($entityTypeId, $entityBundle);


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
  public function addContextsField($entityTypeId, $entityBundle);

  /**
   * Removes contexts field form entity by bundle.
   *
   * @param string $entityTypeId
   *   Entity type id.
   *
   * @param string $entityBundle
   *   Entity bundle name.
   *
   * @return bool|string
   *   Field storage id, needed for batch operation.
   */
  public function removeContextsField($entityTypeId, $entityBundle);

  /**
   * Getter for field config storage.
   *
   * @param string $entityTypeId
   *   Entity type id.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\field\Entity\FieldStorageConfig|null
   *   Field storage config.
   */
  public function getFieldStorageConfig($entityTypeId);

  /**
   * Getter for field config storage.
   *
   * @param string $entityTypeId
   *   Entity type id.
   * @param string $entityBundle
   *   Entity bundle.
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\field\Entity\FieldConfig|null
   *   Field storage config.
   */
  public function getFieldConfig($entityTypeId, $entityBundle);

  /**
   * Get the bundle info of all entity types.
   *
   * @return array
   *   An array of bundle information where the outer array is keyed by entity
   *   type. The next level is keyed by the bundle name. The inner arrays are
   *   associative arrays of bundle information, such as the label for the
   *   bundle.
   */
  public function getContentEntityTypeBundleInfo();

  /**
   * Wrapper for field purge batch handler.
   *
   * @param int $batchSize
   *   Size of batch.
   * @param string $fieldStorageIdentifier
   *   Field storage identifier.
   */
  public function fieldPurgeBatch($batchSize, $fieldStorageIdentifier);
}
