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
   */
  public function updateContextsPath($pid, $contextsPathNew, $contextsPathExisting);

  /**
   * Adds contexts path by pid.
   *
   * @param string|int $pid
   *   Path id.
   * @param string $contextsPath
   *   Contexts path.
   *
   * @throws \Exception
   */
  public function addContextsPath($pid, $contextsPath);

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
