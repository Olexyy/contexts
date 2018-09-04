<?php

namespace Drupal\contexts\EventSubscriber;


use Drupal\contexts\Service\ContextsManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ContextsRequestSubscriber.
 *
 * @package Drupal\contexts\EventSubscriber
 */
class ContextsRequestSubscriber implements EventSubscriberInterface {

  /**
   * Contexts manager.
   *
   * @var ContextsManagerInterface
   */
  protected $contextsManager;

  /**
   * Language manager.
   *
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config factory.
   *
   * @var ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ContextsRequestSubscriber constructor.
   *
   * @param ContextsManagerInterface $contextsManager
   *   Contexts manager.
   * @param LanguageManagerInterface $languageManager
   *   Language manager.
   * @param ConfigFactoryInterface $configFactory
   *   Config factory.
   */
  public function __construct(ContextsManagerInterface $contextsManager,
                              LanguageManagerInterface $languageManager,
                              ConfigFactoryInterface $configFactory) {

    $this->contextsManager = $contextsManager;
    $this->languageManager = $languageManager;
    $this->configFactory = $configFactory;
  }

  /**
   * Initializes the language manager at the beginning of the request.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The Event to process.
   */
  public function onKernelRequestContexts(GetResponseEvent $event) {

    if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
      $uri = $event->getRequest()->getRequestUri();
      $langCode = $this->languageManager->getCurrentLanguage()->getId();
      $prefix = $this->getLanguagePrefix($langCode);
      $this->contextsManager->negotiateContexts($uri, $langCode, $prefix);
    }
  }

  private function getLanguagePrefix($langCode) {

    if ($prefixes = $this->configFactory->get('language.negotiation')->get('url.prefixes')) {
      if (isset($prefixes[$langCode])) {

        return $prefixes[$langCode];
      }
    }

    return NULL;
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {

    // Just after language negotiation (255).
    $events[KernelEvents::REQUEST][] = ['onKernelRequestContexts', 254];

    return $events;
  }
}