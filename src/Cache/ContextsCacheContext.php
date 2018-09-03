<?php

namespace Drupal\contexts\Cache;


use Drupal\contexts\Service\ContextsManagerInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;

/**
 * Class CacheContextsCacheContext.
 *
 * Cache context ID: 'contexts'.
 *
 * @package Drupal\contexts\Cache
 */
class ContextsCacheContext implements CacheContextInterface {

  const CONTEXTS = 'contexts';

  /**
   * Contexts manager.
   *
   * @var ContextsManagerInterface
   */
  protected $contextsManager;

  /**
   * ContextsRequestSubscriber constructor.
   *
   * @param ContextsManagerInterface $contextsManager
   *   Contexts manager.
   */
  public function __construct(ContextsManagerInterface $contextsManager) {

    $this->contextsManager = $contextsManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {

    return t('Contexts');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {

    if ($contextsPath = $this->contextsManager->getContextsPath()) {

      return sha1($contextsPath);
    }

    return sha1('none');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {

    return new CacheableMetadata();
  }

}