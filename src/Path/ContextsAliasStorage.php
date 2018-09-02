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
  public function addContextsPath($pid, $contextsPath, $weight = 0) {

    $this->connection->insert(static::TABLE_CONTEXTS)
      ->fields([
        'pid' => $pid,
        'contexts_path' => $contextsPath,
        'weight' => $weight,
      ])->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function updateContextsPath($pid, $contextsPathNew, $contextsPathExisting, $weight = 0) {

    $this->connection->update(static::TABLE_CONTEXTS)
      ->fields([
        'contexts_path' => $contextsPathNew,
        'weight' => $weight,
        ])
      ->condition('pid', $pid)
      ->condition('contexts_path', $contextsPathExisting)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteContextsPath($pid, $contextsPath = NULL) {

    $query = $this->connection->delete(static::TABLE_CONTEXTS);
    $query->condition('pid', $pid);
    if (!is_null($contextsPath)) {
      $query->condition('contexts_path', $contextsPath);
    }
    $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function save($source, $alias, $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED, $pid = NULL, $contextsPathNew = NULL, $contextsPathExisting = NULL, $weight = 0) {

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
        $this->addContextsPath($pid, $contextsPathNew, $weight);
      }
    }
    else {
      // Fetch the current values so that an update hook can identify what
      // exactly changed.
      try {
        if (!empty($contextsPathExisting)) {
          $original = $this->connection->query(
            'SELECT ua.source, ua.alias, ua.langcode, uac.contexts_path
            FROM {url_alias} AS ua 
            LEFT JOIN {url_alias_contexts} AS uac ON ua.pid = uac.pid
            WHERE ua.pid = :pid AND uac.contexts_path = :contexts_path', [
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
        if (!empty($contextsPathExisting)) {
          $this->updateContextsPath($pid, $contextsPathNew, $contextsPathExisting, $weight);
        }
        else {
          $this->addContextsPath($pid, $contextsPathNew, $weight);
        }
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

    $select = $this->connection->select(static::TABLE, 'ua');
    $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'uac', 'ua.pid = uac.pid');
    foreach ($conditions as $field => $value) {
      if ($field == 'source' || $field == 'alias') {
        // Use LIKE for case-insensitive matching.
        $select->condition('ua.' . $field, $this->connection->escapeLike($value), 'LIKE');
      }
      elseif ($field == 'contexts_path') {
        $select->condition('uac.contexts_path', $value);
      }
      else {
        $select->condition('ua.' . $field, $value);
      }
    }
    try {

      return $select
        ->fields('ua')
        ->fields('uac', ['contexts_path', 'weight'])
        ->orderBy('ua.pid', 'DESC')
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

    $select = $this->connection->select(static::TABLE, 'ua');
    $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'uac', 'ua.pid = uac.pid');
    foreach ($conditions as $field => $value) {
      if ($field == 'source' || $field == 'alias') {
        // Use LIKE for case-insensitive matching.
        $select->condition('ua.' . $field, $this->connection->escapeLike($value), 'LIKE');
      }
      elseif ($field == 'contexts_path') {
        $select->condition('uac.contexts_path', $value);
      }
      else {
        $select->condition('ua.' . $field, $value);
      }
    }
    try {

      return $select
        ->fields('ua')
        ->fields('uac', ['contexts_path', 'weight'])
        ->orderBy('ua.pid', 'ASC')
        ->execute()
        ->fetchAllAssoc('pid', \PDO::FETCH_ASSOC);
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
    $pids = array_keys($paths);
    $deleted = FALSE;
    if (!empty($pids)) {
      try {
        $deleted = $this->connection
          ->delete(static::TABLE)
          ->condition('pid', $pids, 'IN')
          ->execute();
        $this->connection
          ->delete(static::TABLE_CONTEXTS)
          ->condition('pid', $pids, 'IN')
          ->execute();
      } catch (\Exception $e) {
        $this->catchException($e);
      }
      // @todo Switch to using an event for this instead of a hook.
      $this->moduleHandler->invokeAll('path_delete', $paths);
      Cache::invalidateTags(['route_match']);
    }

    return $deleted;
  }

  /**
   * {@inheritdoc}
   */
  public function preloadPathAlias($preloaded, $langcode, $contextsPath = NULL) {

    $langcode_list = [$langcode, LanguageInterface::LANGCODE_NOT_SPECIFIED];
    $select = $this->connection->select(static::TABLE, 'ua')
      ->fields('ua', ['source', 'alias']);

    if (!empty($preloaded)) {
      $conditions = new Condition('OR');
      foreach ($preloaded as $preloaded_item) {
        $conditions->condition('ua.source', $this->connection->escapeLike($preloaded_item), 'LIKE');
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
      $select->orderBy('ua.langcode', 'ASC');
    }
    else {
      $select->orderBy('ua.langcode', 'DESC');
    }

    $select->orderBy('ua.pid', 'ASC');
    $select->condition('ua.langcode', $langcode_list, 'IN');
    if (!empty($contextsPath)) {
      $select->addJoin('LEFT', static::TABLE_CONTEXTS, 'uaс', 'ua.pid = uaс.pid');
      $select->condition('uac.contexts_path', $contextsPath);
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
    $select = $this->connection->select(static::TABLE, 'ua');
    $select->fields('ua', ['alias'])
      ->condition('ua.source', $source, 'LIKE');
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode > LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('ua.langcode', 'DESC');
    }
    else {
      $select->orderBy('ua.langcode', 'ASC');
    }
    $select->orderBy('ua.pid', 'DESC');
    $select->condition('ua.langcode', $langcode_list, 'IN');
    if (!empty($contextsPath)) {
      $joined = $select->addJoin('LEFT', static::TABLE_CONTEXTS, NULL, 'ua.pid = %alias.pid');
      $select->condition($joined . '.contexts_path', $contextsPath);
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
    $select = $this->connection->select(static::TABLE, 'ua')
      ->fields('ua', ['source'])
      ->condition('ua.alias', $alias, 'LIKE');
    if ($langcode == LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      array_pop($langcode_list);
    }
    elseif ($langcode > LanguageInterface::LANGCODE_NOT_SPECIFIED) {
      $select->orderBy('ua.langcode', 'DESC');
    }
    else {
      $select->orderBy('ua.langcode', 'ASC');
    }
    $select->orderBy('ua.pid', 'DESC');
    $select->condition('ua.langcode', $langcode_list, 'IN');
    if (!empty($contextsPath)) {
      $joined = $select->addJoin('LEFT', static::TABLE_CONTEXTS, NULL, 'ua.pid = %alias.pid');
      $select->condition($joined . '.contexts_path', $contextsPath);
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
    $query = $this->connection->select(static::TABLE, 'ua')
      ->condition('ua.alias', $this->connection->escapeLike($alias), 'LIKE')
      ->condition('ua.langcode', $langcode);
    if (!empty($source)) {
      $query->condition('ua.source', $this->connection->escapeLike($source), 'NOT LIKE');
    }
    if (!empty($contextsPath)) {
      $joined = $query->addJoin('INNER', static::TABLE_CONTEXTS, NULL, 'ua.pid = %alias.pid');
      $query->condition($joined . '.contexts_path', $contextsPath);
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

    // Consider including information about contexts.
    // For this path controller needs to be refactored as well.
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
        'weight' => [
          'description' => 'A unique path alias identifier.',
          'type' => 'int',
          'not null' => TRUE,
          'size' => 'normal',
          'unsigned' => TRUE,
          'default' => 0,
        ],
      ],
      'primary key' => [
        'pid', 'contexts_path', 'weight'
      ],
    ];
  }

}
