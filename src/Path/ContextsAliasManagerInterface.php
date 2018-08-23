<?php

namespace Drupal\contexts\Path;

use Drupal\Core\Path\AliasManagerInterface;

/**
 * Interface AliasManagerInterface.
 *
 * @package Drupal\contexts\Path
 */
interface ContextsAliasManagerInterface extends AliasManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function getPathByAlias($alias, $langcode = NULL, $contexts = []);

  /**
   * {@inheritdoc}
   */
  public function getAliasByPath($path, $langcode = NULL, $contexts = []);

  /**
   * Key generator for internal maps.
   *
   * @param string $langcode
   *   Language code.
   * @param array $contexts
   *   Array of existing contexts.
   *
   * @return string
   *   Hash of key.
   */
  public function getMapKey($langcode, array $contexts);

}
