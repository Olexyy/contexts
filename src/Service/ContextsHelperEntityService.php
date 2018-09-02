<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Entity\ContextInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
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

    $contextsPaths = [];
    $contextsNames = [];
    $aggregatedNames = [];
    foreach ($this->getContexts($entity) as $context) {
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

  public function processContextsAliases(EntityInterface $entity) {

  }

}
