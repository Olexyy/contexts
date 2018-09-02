<?php

namespace Drupal\contexts\HookHandler;

use Drupal\contexts\Service\ContextsServiceInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Class ContextsHookHandler.
 *
 * @package Drupal\contexts\HookHandler
 */
class ContextsHookHandler implements ContainerInjectionInterface {

  /**
   * Contexts service.
   *
   * @var ContextsServiceInterface
   */
  protected $contextsService;

  /**
   * ContextsHookHandler constructor.
   *
   * @param ContextsServiceInterface $contextsService
   *   Contexts service.
   */
  public function __construct(ContextsServiceInterface $contextsService) {

    $this->contextsService = $contextsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {

    return new static(
      $container->get('contexts.service')
    );
  }

  /**
   * Implements hook_entity_insert().
   */
  public function hookEntityInsert(EntityInterface $entity) {

    $this->contextsService
      ->getEntityHelper()
      ->processContextsAliases($entity, FALSE);
  }

  /**
   * Implements hook_entity_update().
   */
  public function hookEntityUpdate(EntityInterface $entity) {

    $this->contextsService
      ->getEntityHelper()
      ->processContextsAliases($entity, TRUE);
  }

}
