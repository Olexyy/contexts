<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Path\ContextsAliasStorageInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ContextsHelperBaseService.
 *
 * @package Drupal\contexts\Service
 */
class ContextsHelperBaseService implements ContextsHelperBaseServiceInterface {

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
   * Alias storage.
   *
   * @var ContextsAliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * ContextsService constructor.
   *
   * @param EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param ModuleHandlerInterface $moduleHandler
   *   Module handler.
   * @param EntityTypeBundleInfoInterface $entityTypeBundleInfo
   *   Entity bundle info.
   * @param ContextsAliasStorageInterface $aliasStorage
   *   Contexts alias storage.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              ModuleHandlerInterface $moduleHandler,
                              EntityTypeBundleInfoInterface $entityTypeBundleInfo,
                              ContextsAliasStorageInterface $aliasStorage) {

    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
    $this->aliasStorage = $aliasStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function catchException(\Exception $exception) {

    $this->getLogger('contexts')
      ->error($exception->getMessage());
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
  public function getEntityTypeDefinition($entityTypeId) {

    try {

      return $this->entityTypeManager
        ->getDefinition($entityTypeId);
    }
    catch(\Exception $exception) {
      $this->catchException($exception);

      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityTypeQuery($entityTypeId) {

    try {

      return $this->entityTypeManager
        ->getStorage($entityTypeId)
        ->getQuery();
    }
    catch(\Exception $exception) {
      $this->catchException($exception);

      return NULL;
    }
  }

  /**
   * Getter for module handler.
   *
   * @return ModuleHandlerInterface
   */
  public function getModuleHandler() {

    return $this->moduleHandler;
  }

  /**
   * Getter for bundle info service
   *
   * @return EntityTypeBundleInfoInterface
   *   Bundle info service.
   */
  public function getEntityTypeBundleInfo() {

    return $this->entityTypeBundleInfo;
  }

}
