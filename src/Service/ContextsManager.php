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
   * Inconsistent marker.
   *
   * @var bool
   */
  static $inconsistent;

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
    static::$inconsistent = FALSE;
    $this->helperBaseService = $helperBaseService;
  }

  /**
   * {@inheritdoc}
   */
  public function negotiateContexts($path, $langCode, $langPrefix) {

    if (!$this->isInitialized()) {
      $contexts = [];
      if ($parts = explode('/', trim($path, '/'))) {
        $prefix = array_shift($parts);
        if (($langCode && $prefix == $langCode) || ($langPrefix && $prefix == $langPrefix)) {
          $prefix = array_shift($parts);
        }
        $position = 0;
        while ($context = $this->loadContext($prefix)) {
          if ($context->getPosition() == $position) {
            $contexts[$position] = $context;
            $position ++;
          }
          else {
            $this->setInconsistent(TRUE);
            break;
          }
          $path = '/' . implode('/', $parts);
          $parts = explode('/', trim($path, '/'));
          $prefix = array_shift($parts);
        }
        $this->setContexts($contexts);
      }
      $this->setInitialized(TRUE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processPathInbound($path) {

    $parts = explode('/', trim($path, '/'));
    $prefix = array_shift($parts);
    foreach (static::$contexts as $context) {
      if ($prefix == $context->id()) {
        $path = '/' . implode('/', $parts);
        $parts = explode('/', trim($path, '/'));
        $prefix = array_shift($parts);
      }
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
      $sequence ++;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isValid() {

    return static::$valid;
  }

  /**
   * {@inheritdoc}
   */
  public function isInitialized() {

    return static::$initialized;
  }

  /**
   * {@inheritdoc}
   */
  public function setInitialized($initialized) {

    static::$initialized = $initialized;
  }

  /**
   * {@inheritdoc}
   */
  public function isInconsistent() {

    return static::$inconsistent;
  }

  /**
   * {@inheritdoc}
   */
  public function setInconsistent($inconsistent) {

    static::$inconsistent = $inconsistent;
  }

}
