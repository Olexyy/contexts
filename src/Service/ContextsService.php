<?php

namespace Drupal\contexts\Service;

/**
 * Class ContextsService.
 *
 * @package Drupal\contexts\Service
 */
class ContextsService implements ContextsServiceInterface {

  /**
   * Field helper.
   *
   * @var ContextsHelperFieldServiceInterface
   */
  protected $helperFieldService;

  /**
   * Base helper.
   *
   * @var ContextsHelperBaseServiceInterface
   */
  protected $helperBaseService;

  /**
   * Entity helper.
   *
   * @var ContextsHelperEntityServiceInterface
   */
  protected $helperEntityService;

  /**
   * {@inheritdoc}
   */
  public static function instance() {

    return \Drupal::service('contexts.service');
  }

  /**
   * ContextsService constructor.
   *
   * @param ContextsHelperBaseServiceInterface $helperBaseService
   *   Base helper.
   * @param ContextsHelperFieldServiceInterface $helperFieldService
   *   Field helper.
   * @param ContextsHelperEntityServiceInterface $helperEntityService
   *   Entity helper.
   */
  public function __construct(ContextsHelperBaseServiceInterface $helperBaseService,
                              ContextsHelperFieldServiceInterface $helperFieldService,
                              ContextsHelperEntityServiceInterface $helperEntityService) {

    $this->helperBaseService = $helperBaseService;
    $this->helperFieldService = $helperFieldService;
    $this->helperEntityService = $helperEntityService;
  }

  /**
   * {@inheritdoc}
   */
  public function getHelperBaseService() {

    return $this->helperBaseService;
  }

  /**
   * {@inheritdoc}
   */
  public function getHelperFieldService() {

    return $this->helperFieldService;
  }

  /**
   * {@inheritdoc}
   */
  public function getHelperEntityService() {

    return $this->helperEntityService;
  }

}
