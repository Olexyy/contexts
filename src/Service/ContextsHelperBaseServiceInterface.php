<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Entity\ContextInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Interface ContextsHelperBaseServiceInterface.
 *
 * @package Drupal\contexts\Service
 */
interface ContextsHelperBaseServiceInterface {

  /**
   * Exception logger.
   *
   * @param \Exception $exception
   *   Exception.
   */
  public function catchException(\Exception $exception);

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
   * Getter for entity type definition.
   *
   * @param string $entityTypeId
   *   Entity type id.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface|null
   *   Entity storage if any.
   */
  public function getEntityTypeDefinition($entityTypeId);

  /**
   * Getter for entity tye storage.
   *
   * @param string $entityTypeId
   *   Entity type id.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface|null
   *   Storage if any.
   */
  public function getEntityTypeQuery($entityTypeId);

  /**
   * Getter for module handler.
   *
   * @return ModuleHandlerInterface
   */
  public function getModuleHandler();

  /**
   * Getter for bundle info service
   *
   * @return EntityTypeBundleInfoInterface
   *   Bundle info service.
   */
  public function getEntityTypeBundleInfo();

  /**
   * Getter for all allowed contexts paths for given contexts.
   *
   * @param ContextInterface[] $contexts
   *   Given entity.
   *
   * @return array|string[]
   *   Array of contexts paths.
   */
  public function getContextsPaths(array $contexts);

}
