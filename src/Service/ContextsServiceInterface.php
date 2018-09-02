<?php

namespace Drupal\contexts\Service;

/**
 * Interface ContextsServiceInterface.
 *
 * @package Drupal\contexts\Service
 */
interface ContextsServiceInterface {

  /**
   * Basic field name.
   */
  const FIELD_NAME = 'contexts';

  /**
   * Singleton.
   *
   * @return $this
   *   This as singleton.
   */
  public static function instance();

  /**
   * Helper base service.
   *
   * @return ContextsHelperBaseServiceInterface
   */
  public function getBaseHelper();

  /**
   * Helper field service.
   *
   * @return ContextsHelperFieldServiceInterface
   */
  public function getFieldHelper();

  /**
   * Helper entity service.
   *
   * @return ContextsHelperEntityServiceInterface
   */
  public function getEntityHelper();

}
