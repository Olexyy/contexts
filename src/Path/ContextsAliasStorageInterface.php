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
   * {@inheritdoc}
   */
  public function aliasExists($alias, $langcode, $source = NULL, $contexts = []);

  /**
   * Schema definition function.
   *
   * @return array
   *   Schema definition.
   */
  public static function schemaDefinitionContexts();

  /**
   * Loads all records by condition.
   *
   * @param array $conditions
   *   Conditions array.
   *
   * @return array
   *   Array of results if any.
   */
  public function loadAll($conditions);

}
