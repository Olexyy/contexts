<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Entity\ContextInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Interface ContextsHelperEntityServiceInterface.
 *
 * @package Drupal\contexts\Service
 */
interface ContextsHelperEntityServiceInterface {

  /**
   * Tests entity against having 'contexts' field.
   *
   * @param EntityInterface $entity
   *   Given entity.
   *
   * @return bool
   *   Test result.
   */
  public function isContextAware(EntityInterface $entity);

  /**
   * Tests entity against having 'path' field.
   *
   * @param EntityInterface $entity
   *   Given entity.
   *
   * @return bool
   *   Test result.
   */
  public function hasPath(EntityInterface $entity);

  /**
   * Getter for entity contexts.
   *
   * @param EntityInterface $entity
   *   Target entity.
   *
   * @return array|ContextInterface[]
   *   Contexts of entity.
   */
  public function getContexts(EntityInterface $entity);

  /**
   * Processes context aliases.
   *
   * @param EntityInterface $entity
   *   Target entity.
   */
  public function processContextsAliases(EntityInterface $entity);

}
