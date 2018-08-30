<?php

namespace Drupal\contexts\Path;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasWhitelistInterface;
use Drupal\Core\Path\AliasManager;

/**
 * The default alias manager implementation.
 */
class ContextsAliasManager extends AliasManager implements ContextsAliasManagerInterface{

  /**
   * Contexts alias storage.
   *
   * @var ContextsAliasStorageInterface
   */
  protected $storage;

  /**
   * Constructs an ContextsAliasManager.
   *
   * {@inheritdoc}
   */
  public function __construct(ContextsAliasStorageInterface $storage,
                              AliasWhitelistInterface $whitelist,
                              LanguageManagerInterface $language_manager,
                              CacheBackendInterface $cache) {

    parent::__construct($storage, $whitelist, $language_manager, $cache);
  }

  /**
   * {@inheritdoc}
   */
  public function getMapKey($langcode, $contextsPath) {

    return sha1($contextsPath . $langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function getPathByAlias($alias, $langcode = NULL, $contextsPath = NULL) {
    // If no language is explicitly specified we default to the current URL
    // language. If we used a language different from the one conveyed by the
    // requested URL, we might end up being unable to check if there is a path
    // alias matching the URL path.
    $langcode = $langcode ?: $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
    // TODO get default | current contexts here...
    // Contexts manager service...
    $mapKey = $this->getMapKey($langcode, $contextsPath);

    // If we already know that there are no paths for this alias simply return.
    if (empty($alias) || !empty($this->noPath[$mapKey][$alias])) {

      return $alias;
    }

    // Look for the alias within the cached map.
    if (isset($this->lookupMap[$mapKey]) && ($path = array_search($alias, $this->lookupMap[$mapKey]))) {

      return $path;
    }

    // Look for path in storage.
    if ($path = $this->storage->lookupPathSource($alias, $langcode, $contextsPath)) {
      $this->lookupMap[$mapKey][$path] = $alias;

      return $path;
    }

    // We can't record anything into $this->lookupMap because we didn't find any
    // paths for this alias. Thus cache to $this->noPath.
    $this->noPath[$mapKey][$alias] = TRUE;

    return $alias;
  }

  /**
   * {@inheritdoc}
   */
  public function getAliasByPath($path, $langcode = NULL, $contextsPath = NULL) {
    if ($path[0] !== '/') {
      throw new \InvalidArgumentException(sprintf('Source path %s has to start with a slash.', $path));
    }
    // If no language is explicitly specified we default to the current URL
    // language. If we used a language different from the one conveyed by the
    // requested URL, we might end up being unable to check if there is a path
    // alias matching the URL path.
    $langcode = $langcode ?: $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
    // TODO get default| current contexts here...
    // Contexts manager service...
    $mapKey = $this->getMapKey($langcode, $contextsPath);

    // Check the path whitelist, if the top-level part before the first /
    // is not in the list, then there is no need to do anything further,
    // it is not in the database.
    if ($path === '/' || !$this->whitelist->get(strtok(trim($path, '/'), '/'))) {
      return $path;
    }

    // During the first call to this method per language, load the expected
    // paths for the page from cache.
    if (empty($this->langcodePreloaded[$mapKey])) {
      $this->langcodePreloaded[$mapKey] = TRUE;
      $this->lookupMap[$mapKey] = [];

      // Load the cached paths that should be used for preloading. This only
      // happens if a cache key has been set.
      if ($this->preloadedPathLookups === FALSE) {
        $this->preloadedPathLookups = [];
        if ($this->cacheKey) {
          if ($cached = $this->cache->get($this->cacheKey)) {
            $this->preloadedPathLookups = $cached->data;
          }
          else {
            $this->cacheNeedsWriting = TRUE;
          }
        }
      }

      // Load paths from cache.
      if (!empty($this->preloadedPathLookups[$mapKey])) {
        $this->lookupMap[$mapKey] = $this->storage->preloadPathAlias($this->preloadedPathLookups[$mapKey], $langcode, $contextsPath);
        // Keep a record of paths with no alias to avoid querying twice.
        $this->noAlias[$mapKey] = array_flip(array_diff_key($this->preloadedPathLookups[$mapKey], array_keys($this->lookupMap[$mapKey])));
      }
    }

    // If we already know that there are no aliases for this path simply return.
    if (!empty($this->noAlias[$mapKey][$path])) {

      return $path;
    }

    // If the alias has already been loaded, return it from static cache.
    if (isset($this->lookupMap[$mapKey][$path])) {

      return $this->lookupMap[$mapKey][$path];
    }

    // Try to load alias from storage.
    if ($alias = $this->storage->lookupPathAlias($path, $langcode, $contextsPath)) {
      $this->lookupMap[$mapKey][$path] = $alias;

      return $alias;
    }

    // We can't record anything into $this->lookupMap because we didn't find any
    // aliases for this path. Thus cache to $this->noAlias.
    $this->noAlias[$mapKey][$path] = TRUE;

    return $path;
  }

}
