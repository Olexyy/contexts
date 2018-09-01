<?php

namespace Drupal\contexts\Service;

/**
 * Interface ContextsServiceInterface.
 *
 * @package Drupal\contexts\Service
 */
interface ContextsServiceInterface {

  /**
   * Basic field name.
   */
  const FIELD_NAME = 'contexts';

  /**
   * Singleton.
   *
   * @return $this
   *   This as singleton.
   */
  public static function instance();

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
   * @return bool
   *   Execution result.
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
   * Getter for entity storage.
   *
   * @param string $entityType
   *   Entity type.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface|null
   *   Entity storage if any.
   */
  public function getEntityTypeStorage($entityType);

  /**
   * Getter for entity type definitions.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   Entity storage if any.
   */
  public function getEntityTypeDefinitions();

  /**
   * Exception logger.
   *
   * @param \Exception $exception
   *   Exception.
   */
  public function catchException(\Exception $exception);

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
}
