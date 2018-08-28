<?php

namespace Drupal\contexts;

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

    $definition = $container->getDefinition('path.alias_storage');
    $definition->setClass(ContextsAliasStorage::class);
  }

}
