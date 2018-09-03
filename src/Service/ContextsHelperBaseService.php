<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Entity\ContextInterface;
use Drupal\contexts\Path\ContextsAliasStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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

  /**
   * Getter for all allowed contexts paths for given contexts.
   *
   * @param ContextInterface[] $contexts
   *   Given entity.
   *
   * @return array|string[]
   *   Array of contexts paths.
   */
  public function getContextsPaths(array $contexts) {

    $contextsPaths = [];
    $contextsNames = [];
    $aggregatedNames = [];
    foreach ($contexts as $context) {
      $contextsNames[$context->getPosition()][] = $context->id();
    }
    foreach ($contextsNames as $position => $names) {
      foreach ($names as $name) {
        if (!$position) {
          $aggregatedNames[] = $name;
          $contextsPaths[] = $name;
        }
        else {
          foreach ($aggregatedNames as &$aggregatedName) {
            $aggregatedName .= '/' . $name;
            $contextsPaths[] = $aggregatedName;
          }
        }
      }
    }

    return $contextsPaths;
  }

}
