<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Entity\ContextInterface;

/**
 * Class ContextsManager.
 *
 * @package Drupal\contexts\Service
 */
class ContextsManager implements ContextsManagerInterface {

  /**
   * Contexts static array.
   *
   * @var array
   */
  static $contexts;

  /**
   * Valid marker.
   *
   * @var bool
   */
  static $valid;

  /**
   * Initialized marker.
   *
   * @var bool
   */
  static $initialized;

  /**
   * Base helper service.
   *
   * @var ContextsHelperBaseService
   */
  protected $helperBaseService;

  /**
   * ContextsManager constructor.
   *
   * @param ContextsHelperBaseService $helperBaseService
   *   Base helper service.
   */
  public function __construct(ContextsHelperBaseService $helperBaseService) {

    static::$contexts = [];
    static::$valid = TRUE;
    static::$initialized = FALSE;
    $this->helperBaseService = $helperBaseService;
  }

  /**
   * @param string $path
   *   Given path.
   */
  public function negotiateContexts($path) {

    $contexts = [];
    $parts = explode('/', trim($path, '/'));
    $prefix = array_shift($parts);
    // Search prefix within added languages.
    while ($context = $this->loadContext($prefix)) {
      $contexts[] = $context;
      $path = '/' . implode('/', $parts);
      $parts = explode('/', trim($path, '/'));
      $prefix = array_shift($parts);
    }
    $this->setContexts($contexts);
    static::$initialized = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function processPathInbound($path) {

    $contexts = [];
    $parts = explode('/', trim($path, '/'));
    $prefix = array_shift($parts);
    if (static::$initialized) {
      foreach (static::$contexts as $context) {
        if ($prefix == $context->id()) {
          $path = '/' . implode('/', $parts);
          $parts = explode('/', trim($path, '/'));
          $prefix = array_shift($parts);
        }
      }
    }
    else {
      while ($context = $this->loadContext($prefix)) {
        $contexts[] = $context;
        $path = '/' . implode('/', $parts);
        $parts = explode('/', trim($path, '/'));
        $prefix = array_shift($parts);
      }
      $this->setContexts($contexts);
      static::$initialized = TRUE;
    }
    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function loadContext($contextId) {

    return $this->helperBaseService
      ->getEntityTypeStorage('context')
      ->load($contextId);
  }

  /**
   * {@inheritdoc}
   */
  public function getContexts() {

    return static::$contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextsPath(array $contexts = NULL) {

    if (is_null($contexts)) {
      $contexts = static::$contexts;
    }
    if (!$this->validate($contexts)) {

      return NULL;
    }
    if ($paths = $this->helperBaseService->getContextsPaths($contexts)) {

      return $paths[count($paths)-1];
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setContexts(array $contexts) {

    static::$contexts = $contexts;
    static::$valid = $this->validate();
  }

  /**
   * {@inheritdoc}
   */
  public function insertContext(ContextInterface $context) {

    static::$contexts[$context->getPosition()] = $context;
    static::$valid = $this->validate();
  }

  /**
   * {@inheritdoc}
   */
  public function popContext() {

    return array_pop(static::$contexts);
  }

  /**
   * {@inheritdoc}
   */
  public function hasContext(ContextInterface $context) {

    foreach (static::$contexts as $key => $contextItem) {
      if ($context->id() == $contextItem->id()) {

        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $contexts = NULL) {

    if (is_null($contexts)) {
      $contexts = static::$contexts;
    }
    $sequence = 0;
    foreach ($contexts as $key => $contextItem) {
      if (!$contextItem instanceof ContextInterface) {

        return FALSE;
      }
      elseif ($contextItem->getPosition() != $key) {

        return FALSE;
      }
      elseif($key != $sequence) {

        return FALSE;
      }
      $sequence++;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {

    return static::$valid;
  }

}
