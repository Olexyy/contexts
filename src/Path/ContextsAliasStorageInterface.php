<?php

namespace Drupal\contexts\Path;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Path\AliasStorageInterface;

interface ContextsAliasStorageInterface extends AliasStorageInterface {

  /**
   * Contexts table name definition.
   */
  const TABLE_CONTEXTS = 'url_alias_contexts';

  /**
   * Adds contexts path by pid.
   *
   * @param string|int $pid
   *   Path id.
   * @param string $contextsPathNew
   *   New contexts path.
   * @param string $contextsPathExisting
   *   Existing contexts path.
   * @param int $weight
   *   Path alias weight.
   */
  public function updateContextsPath($pid, $contextsPathNew, $contextsPathExisting, $weight = 0);

  /**
   * Adds contexts path by pid.
   *
   * @param string|int $pid
   *   Path id.
   * @param string $contextsPath
   *   Contexts path.
   * @param int $weight
   *   Path alias weight.
   */
  public function addContextsPath($pid, $contextsPath, $weight = 0);

  /**
   * Helper to delete specific context paths.
   *
   * @param string $pid
   *   Path id.
   * @param null $contextsPath
   *   Specific path if needed.
   */
  public function deleteContextsPath($pid, $contextsPath = NULL);

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

  /**
   * {@inheritdoc}
   */
  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL, $contexts = []);

  /**
   * {@inheritdoc}
   */
  public function aliasExists($alias, $langcode, $source = NULL, $contexts = NULL);

  /**
   * {@inheritdoc}
   */
  public function lookupPathSource($path, $langcode, $contextsPath = NULL);

  /**
   * {@inheritdoc}
   */
  public function lookupPathAlias($path, $langcode, $contextsPath = NULL);

  /**
   * {@inheritdoc}
   */
  public function preloadPathAlias($preloaded, $langcode, $contextsPath = NULL);

}
