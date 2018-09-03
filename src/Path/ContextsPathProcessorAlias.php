<?php

namespace Drupal\contexts\Path;


use Drupal\contexts\Cache\ContextsCacheContext;
use Drupal\contexts\Service\ContextsManagerInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContextsPathProcessorAlias.
 *
 * @package Drupal\contexts\Path
 */
class ContextsPathProcessorAlias implements InboundPathProcessorInterface, OutboundPathProcessorInterface {

  /**
   * An alias manager for looking up the system path.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Contexts manager.
   *
   * @var ContextsManagerInterface
   */
  protected $contextsManager;

  /**
   * Constructs a PathProcessorAlias object.
   *
   * @param ContextsAliasManagerInterface $alias_manager
   *   An alias manager for looking up the system path.
   * @param ContextsManagerInterface $contextsManager
   *   Contexts manager.
   */
  public function __construct(ContextsAliasManagerInterface $alias_manager,
                              ContextsManagerInterface $contextsManager) {

    $this->aliasManager = $alias_manager;
    $this->contextsManager = $contextsManager;
  }

  /**
   * {@inheritdoc}
   */
  public function processInbound($path, Request $request) {

    // Trigger manager to process path.
    $path = $this->contextsManager->processPathInbound($path);
    // Switch to system path if any.
    $path = $this->aliasManager->getPathByAlias($path, NULL, $this->contextsManager->getContextsPath());

    return $path;
  }

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {

    // Define contexts path.
    if (!empty($options['contexts']) && is_array($options['contexts'])) {
      $contexts = $options['contexts'];
    }
    else {
      $contexts = $this->contextsManager->getContexts();
    }
    // Add tags as contexts as we want to cache by it.
    if ($bubbleable_metadata) {
      $bubbleable_metadata->addCacheContexts([ContextsCacheContext::CONTEXTS]);
    }
    $contextsPath = $this->contextsManager->getContextsPath($contexts);
    // Handle alias.
    if (empty($options['alias'])) {
      $langcode = isset($options['language']) ? $options['language']->getId() : NULL;
      $path = $this->aliasManager->getAliasByPath($path, $langcode, $contextsPath);
    }
    // Handle path prefix.
    if ($contextsPath) {
      $path = '/' . $contextsPath . $path;
    }


    return $path;
  }

}
