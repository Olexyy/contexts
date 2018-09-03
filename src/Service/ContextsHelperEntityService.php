<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Path\ContextsAliasStorageInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ContextsHelperEntityService.
 *
 * @package Drupal\contexts\Service
 */
class ContextsHelperEntityService implements ContextsHelperEntityServiceInterface {

  use StringTranslationTrait;

  /**
   * Base helper.
   *
   * @var ContextsHelperBaseServiceInterface
   */
  protected $helperBaseService;

  /**
   * Alias storage.
   *
   * @var ContextsAliasStorageInterface
   */
  protected $aliasStorage;

  /**
   * ContextsHelperFieldService constructor.
   *
   * @param ContextsHelperBaseServiceInterface $helperBaseService
   *   Base helper.
   * @param ContextsAliasStorageInterface $aliasStorage
   *   Alias storage.
   */
  public function __construct(ContextsHelperBaseServiceInterface $helperBaseService,
                              ContextsAliasStorageInterface $aliasStorage) {

    $this->helperBaseService = $helperBaseService;
    $this->aliasStorage = $aliasStorage;
  }

  /**
   * {@inheritdoc}
   */
  public function isContextAware(EntityInterface $entity) {

    if ($entity instanceof ContentEntityInterface) {

      return $entity->hasField(ContextsServiceInterface::FIELD_NAME);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPath(EntityInterface $entity) {

    if ($entity instanceof ContentEntityInterface) {
      if ($entity->hasField('path')) {
        if ($field = $entity->getFieldDefinition('path')) {
          if ($field->getType() == 'path') {

            return TRUE;
          }
        }
      }
    }

    return FALSE;
  }

  public function getPathAlias(EntityInterface $entity) {

    if ($this->hasPath($entity)) {
      if (isset($entity->path)) {

        return $entity->path->alias;
      }
    }

    return NULL;
  }

  public function getPathPid(EntityInterface $entity) {

    if ($this->hasPath($entity)) {
      if (isset($entity->path)) {

        return $entity->path->pid;
      }
    }

    return NULL;
  }

  public function getLangCode(EntityInterface $entity) {

    if ($this->hasPath($entity)) {
      if (isset($entity->path)) {

        return $entity->path->getLangcode();
      }
    }

    return Language::LANGCODE_NOT_SPECIFIED;
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts(EntityInterface $entity) {

    $contexts = [];
    if ($this->isContextAware($entity)) {
      foreach ($entity->{ContextsServiceInterface::FIELD_NAME}->referencedEntities() as $context) {
        $contexts[] = $context;
      }
    }

    return $contexts;
  }

  /**
   * Getter for all allowed contexts paths for given entity.
   *
   * @param EntityInterface $entity
   *   Given entity.
   *
   * @return array|string[]
   *   Array of contexts paths.
   */
  public function getContextsPaths(EntityInterface $entity) {

    return $this->helperBaseService->getContextsPaths($this->getContexts($entity));
  }

  /**
   * {@inheritdoc}
   */
  public function processContextsAliases(EntityInterface $entity, $update) {

    if ($this->isContextAware($entity) && $this->hasPath($entity)) {
      // Predictable behavior is only for insert.
      if (!$update) {
        // If we have any pid after insert, try to persist contexts.
        if ($pid = $this->getPathPid($entity)) {
          foreach ($this->getContextsPaths($entity) as $weight => $contextsPath) {
            $this->aliasStorage->addContextsPath($pid, $contextsPath, $weight);
          }
        }
      }
      else {
        if ($alias = $this->getPathAlias($entity)) {
          $newAlias = FALSE;
          if (!$pid = $this->getPathPid($entity)) {
            // Property 'pid' not updated on update!... so figure it out.
            $aliasInfo = $this->aliasStorage->load([
              'source' => '/' . $entity->urlInfo()->getInternalPath(),
              'langcode' => $this->getLangCode($entity),
            ]);
            if ($aliasInfo) {
              $pid = $aliasInfo['pid'];
              $newAlias = TRUE;
            }
          }
          if ($pid) {
            if ($newAlias) {
              foreach ($this->getContextsPaths($entity) as $weight => $contextsPath) {
                $this->aliasStorage->addContextsPath($pid, $contextsPath, $weight);
              }
            }
            else {
              // Define if any changes and re-persist them all if any changes.
              // Implement array diff.
              $this->aliasStorage->deleteContextsPath($pid);
              foreach ($this->getContextsPaths($entity) as $weight => $contextsPath) {
                $this->aliasStorage->addContextsPath($pid, $contextsPath, $weight);
              }
            }
          }
        }
      }
    }
  }

}
