<?php

namespace Drupal\contexts\Path;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorageInterface;

interface ContextsAliasStorageInterface extends AliasStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL, $contexts = []);

  /**
   * Getter for contexts hash.
   *
   * @param array $contexts
   *   Array of contexts.
   *
   * @return string
   *   Hash.
   */
  public function getContextsHash(array $contexts);


}
