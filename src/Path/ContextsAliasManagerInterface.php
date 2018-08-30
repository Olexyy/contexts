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
  public function getPathByAlias($alias, $langcode = NULL, $contextsPath = NULL);

  /**
   * {@inheritdoc}
   */
  public function getAliasByPath($path, $langcode = NULL, $contextsPath = NULL);

  /**
   * Key generator for internal maps.
   *
   * @param string $langcode
   *   Language code.
   * @param string $contextsPath
   *   Contexts path.
   *
   * @return string
   *   Hash of key.
   */
  public function getMapKey($langcode, $contextsPath);

}
