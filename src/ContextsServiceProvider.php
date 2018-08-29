<?php

namespace Drupal\contexts;

use Drupal\contexts\Path\ContextsAliasManager;
use Drupal\contexts\Path\ContextsAliasStorage;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Class ContextsServiceProvider.
 *
 * @package Drupal\contexts
 */
class ContextsServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {

    $aliasStorage = $container->getDefinition('path.alias_storage');
    $aliasStorage->setClass(ContextsAliasStorage::class);

    $aliasManager = $container->getDefinition('path.alias_manager');
    $aliasManager->setClass(ContextsAliasManager::class);
  }

}
