<?php

namespace Drupal\contexts\Path;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Path\AliasStorage;

/**
 * Provides a class for CRUD operations on path aliases.
 *
 * {@inheritdoc}
 */
class ContextsAliasStorage extends AliasStorage implements ContextsAliasStorageInterface {

  /**
   * Contexts table name definition.
   */
  const TABLE_CONTEXTS = 'url_alias_contexts';

  /**
   * ContextsAliasStorage constructor.
   *
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, ModuleHandlerInterface $module_handler) {

    parent::__construct($connection, $module_handler);
  }

  /**
   * {@inheritdoc}
   */
  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL, $contextsPathNew = NULL, $contextsPathExisting = NULL) {

    if ($source[0] !== '/') {
      throw new \InvalidArgumentException(sprintf('Source path %s has to start with a slash.', $source));
    }

    if ($alias[0] !== '/') {
      throw new \InvalidArgumentException(sprintf('Alias path %s has to start with a slash.', $alias));
    }

    $fields = [
      'source' => $source,
      'alias' => $alias,
      'langcode' => $langcode,
    ];

    // Insert or update the alias.
    if (empty($pid)) {
      $try_again = FALSE;
      try {
        $query = $this->connection->insert(static::TABLE)
          ->fields($fields);
        $pid = $query->execute();
      }
      catch (\Exception $e) {
        // If there was an exception, try to create the table.
        if (!$try_again = $this->ensureTableExists()) {
          // If the exception happened for other reason than the missing table,
          // propagate the exception.
          throw $e;
        }
      }
      // Now that the table has been created, try again if necessary.
      if ($try_again) {
        $query = $this->connection->insert(static::TABLE)
          ->fields($fields);
        $pid = $query->execute();
      }

      $fields['pid'] = $pid;
      $operation = 'insert';
      // Insert into additional table.
      if (!empty($contextsPathNew)) {
        $this->connection->insert(static::TABLE_CONTEXTS)
          ->fields([
            'pid' => $pid,
            'contexts_path' => $contextsPathNew,
          ])->execute();
      }
    }
    else {
      // Fetch the current values so that an update hook can identify what
      // exactly changed.
      try {
        if (!empty($contextsPathExisting)) {
          $original = $this->connection->query(
            'SELECT a.source, a.alias, a.langcode, c.contexts_path FROM {url_alias} AS a LEFT JOIN
            {url_alias_contexts} AS c ON a.pid = c.pid WHERE a.pid = :pid AND c.contexts_path = :contexts_path', [
              ':pid' => $pid,
              ':contexts_path' => $contextsPathExisting,
            ])->fetchAssoc();
        }
        else {
          $original = $this->connection->query('SELECT source, alias, langcode FROM {url_alias} WHERE pid = :pid', [
            ':pid' => $pid])->fetchAssoc();
        }
      }
      catch (\Exception $e) {
        $this->catchException($e);
        $original = FALSE;
      }
      $fields['pid'] = $pid;
      $query = $this->connection->update(static::TABLE)
        ->fields($fields)
        ->condition('pid', $pid);
      $pid = $query->execute();
      if (!empty($contextsPathNew)) {
        $this->connection->update(static::TABLE_CONTEXTS)
          ->fields(['contexts_path' => $contextsPathNew])
          ->condition('pid', $pid)
          ->condition('contexts_path', $contextsPathNew)
          ->execute();
      }
      $fields['original'] = $original;
      $operation = 'update';
    }
    if ($pid) {
      // @todo Switch to using an event for this instead of a hook.
      $this->moduleHandler->invokeAll('path_' . $operation, [$fields]);
      Cache::invalidateTags(['route_match']);

      return $fields;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function load($conditions) {

    $select = $this->connection->select(static::TABLE, 'a');
    $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'с', 'a.pid = с.pid');
    foreach ($conditions as $field => $value) {
      if ($field == 'source' || $field == 'alias') {
        // Use LIKE for case-insensitive matching.
        $select->condition('a.' . $field, $this->connection->escapeLike($value), 'LIKE');
      }
      elseif ($field == 'contexts_path') {
        $select->condition('c.contexts_path', $value);
      }
      else {
        $select->condition('a' . $field, $value);
      }
    }
    try {

      return $select
        ->fields(static::TABLE)
        ->orderBy('a.pid', 'DESC')
        ->range(0, 1)
        ->execute()
        ->fetchAssoc();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadAll($conditions) {

    $select = $this->connection->select(static::TABLE, 'a');
    $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'с', 'a.pid = с.pid');
    foreach ($conditions as $field => $value) {
      if ($field == 'source' || $field == 'alias') {
        // Use LIKE for case-insensitive matching.
        $select->condition('a.' . $field, $this->connection->escapeLike($value), 'LIKE');
      }
      elseif ($field == 'contexts_path') {
        $select->condition('c.contexts_path', $value);
      }
      else {
        $select->condition('a' . $field, $value);
      }
    }
    try {

      return $select
        ->fields(static::TABLE)
        ->orderBy('a.pid', 'DESC')
        ->execute()
        ->fetchAllAssoc('pid');
    }
    catch (\Exception $e) {
      $this->catchException($e);

      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete($conditions) {

    $paths = $this->loadAll($conditions);
    $query = $this->connection->delete(static::TABLE);
    foreach ($conditions as $field => $value) {
      if ($field == 'source' || $field == 'alias') {
        // Use LIKE for case-insensitive matching.
        $query->condition($field, $this->connection->escapeLike($value), 'LIKE');
      }
      else {
        $query->condition($field, $value);
      }
    }
    try {
      $deleted = $query->execute();
      if (!empty($paths)) {
        $this->connection->delete(static::TABLE_CONTEXTS)
          ->condition('pid', array_keys($paths), 'IN')
          ->execute();
      }
    }
    catch (\Exception $e) {
      $this->catchException($e);
      $deleted = FALSE;
    }
    // @todo Switch to using an event for this instead of a hook.
    $this->moduleHandler->invokeAll('path_delete', $paths);
    Cache::invalidateTags(['route_match']);

    return $deleted;
  }

  /**
   * {@inheritdoc}
   */
  public function preloadPathAlias($preloaded, $langcode, $contextsPath = NULL) {

    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
    $select = $this->connection->select(static::TABLE, 'a')
      ->fields('a', ['source', 'alias']);

    if (!empty($preloaded)) {
      $conditions = new Condition('OR');
      foreach ($preloaded as $preloaded_item) {
        $conditions->condition('a.source', $this->connection->escapeLike($preloaded_item), 'LIKE');
      }
      $select->condition($conditions);
    }

    // Always get the language-specific alias before the language-neutral one.
    // For example 'de' is less than 'und' so the order needs to be ASC, while
    // 'xx-lolspeak' is more than 'und' so the order needs to be DESC. We also
    // order by pid ASC so that fetchAllKeyed() returns the most recently
    // created alias for each source. Subsequent queries using fetchField() must
    // use pid DESC to have the same effect.
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode < LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('a.langcode', 'ASC');
    }
    else {
      $select->orderBy('a.langcode', 'DESC');
    }

    $select->orderBy('a.pid', 'ASC');
    $select->condition('a.langcode', $langcode_list, 'IN');
    if (!empty($contextsPath)) {
      $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'с', 'a.pid = с.pid');
      $select->condition('c.contexts_path', $contextsPath);
    }
    try {
      return $select->execute()->fetchAllKeyed();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function lookupPathAlias($path, $langcode, $contextsPath = NULL) {

    $source = $this->connection->escapeLike($path);
    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
    // See the queries above. Use LIKE for case-insensitive matching.
    $select = $this->connection->select(static::TABLE, 'a');
      $select->fields('a', ['alias'])
      ->condition('a.source', $source, 'LIKE');
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode > LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('a.langcode', 'DESC');
    }
    else {
      $select->orderBy('a.langcode', 'ASC');
    }
    $select->orderBy('a.pid', 'DESC');
    $select->condition('a.langcode', $langcode_list, 'IN');
    if (!empty($contextsPath)) {
      $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'с', 'a.pid = с.pid');
      $select->condition('c.contexts_path', $contextsPath);
    }
    try {
      return $select->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function lookupPathSource($path, $langcode, $contextsPath = NULL) {

    $alias = $this->connection->escapeLike($path);
    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
    // See the queries above. Use LIKE for case-insensitive matching.
    $select = $this->connection->select(static::TABLE, 'a')
      ->fields('a', ['source'])
      ->condition('a.alias', $alias, 'LIKE');
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode > LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('a.langcode', 'DESC');
    }
    else {
      $select->orderBy('a.langcode', 'ASC');
    }
    $select->orderBy('a.pid', 'DESC');
    $select->condition('a.langcode', $langcode_list, 'IN');
    if (!empty($contextsPath)) {
      $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'с', 'a.pid = с.pid');
      $select->condition('c.contexts_path', $contextsPath);
    }
    try {
      return $select->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function aliasExists($alias, $langcode, $source = NULL, $contextsPath = NULL) {

    // Use LIKE and NOT LIKE for case-insensitive matching.
    $query = $this->connection->select(static::TABLE, 'a')
      ->condition('a.alias', $this->connection->escapeLike($alias), 'LIKE')
      ->condition('a.langcode', $langcode);
    if (!empty($source)) {
      $query->condition('a.source', $this->connection->escapeLike($source), 'NOT LIKE');
    }
    if (!empty($contextsPath)) {
      $query->addJoin('INNER', static::TABLE_CONTEXTS, 'с', 'a.pid = с.pid');
      $query->condition('c.context', $contextsPath, 'IN');
    }
    $query->addExpression('1');
    $query->range(0, 1);
    try {
      return (bool) $query->execute()->fetchField();
    }
    catch (\Exception $e) {
      $this->catchException($e);

      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasesForAdminListing($header, $keys = NULL) {

    // TODO implement including information about contexts.
    return parent::getAliasesForAdminListing($header, $keys);
  }

  /**
   * {@inheritdoc}
   */
  public static function schemaDefinitionContexts() {

    return [
      'description' => 'A relation between url aliases and contexts.',
      'fields' => [
        'pid' => [
          'description' => 'A unique path alias identifier.',
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'unsigned' => TRUE,
        ],
        'contexts_path' => [
          'description' => 'Contexts path for alias ("/" imploded).',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ],
      ],
      'primary key' => [
        'pid', 'contexts_path'
      ],
    ];
  }
}
