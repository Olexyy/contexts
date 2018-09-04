<?php

namespace Drupal\contexts\Service;

use Drupal\contexts\Entity\ContextInterface;

/**
 * Interface ContextsManagerInterface.
 *
 * @package Drupal\contexts\Service
 */
interface ContextsManagerInterface {

  /**
   * Negotiate contexts based on path.
   *
   * @param string $path
   *   Given path.
   * @param string $langCode
   *   Given language code.
   * @param string|null $prefix
   *   Given language prefix.
   */
  public function negotiateContexts($path, $langCode, $prefix);

  /**
   * Path processor function.
   *
   * @param string $path
   *    Basic path.
   *
   * @return string
   *    Processed path.
   */
  public function processPathInbound($path);

  /**
   * Getter for contexts.
   *
   * @return array|ContextInterface[]
   *   Contexts.
   */
  public function getContexts();

  /**
   * Getter for contexts path string.
   *
   * @param array|null $contexts
   *   Given contexts or internal.
   *
   * @return string|null
   *   Path if any.
   */
  public function getContextsPath(array $contexts = NULL);

  /**
   * Contexts bulk setter.
   *
   * @param array $contexts
   *   Contexts array.
   */
  public function setContexts(array $contexts);

  /**
   * Pops last context form contexts.
   *
   * @return ContextInterface
   *   Popped item.
   */
  public function popContext();

  /**
   * Inserts context by its position.
   *
   * @param ContextInterface $context
   *   Given context.
   */
  public function insertContext(ContextInterface $context);

  /**
   * Predicate to define if contexts is in contexts array.
   *
   * @param ContextInterface $context
   *   Context tot search for.
   *
   * @return bool
   *   Test result.
   */
  public function hasContext(ContextInterface $context);

  /**
   * Context loader.
   *
   * @param string $contextId
   *   Context id.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null|ContextInterface
   */
  public function loadContext($contextId);

  /**
   * Validator function.
   *
   * Validates against:
   *  - type of item;
   *  - keys sequence;
   *  - key to item position equality;
   *
   * @param array|null $contexts
   *   Given contexts or internal.
   */
  public function validate(array $contexts = NULL);

  /**
   * Predicate to define if contexts are valid.
   *
   * @return bool
   *   Test result.
   */
  public function isValid();

  /**
   * Getter.
   *
   * @return bool
   *   Initialized state.
   */
  public function isInitialized();

  /**
   * Setter.
   *
   * @param bool $initialized
   *   Initialized state.
   */
  public function setInitialized($initialized);

  /**
   * Getter.
   *
   * @return bool
   *   Inconsistent state.
   */
  public function isInconsistent();

  /**
   * Setter.
   *
   * @param bool $inconsistent
   *   Inconsistent state.
   */
  public function setInconsistent($inconsistent);

}
