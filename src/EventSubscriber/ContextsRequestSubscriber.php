<?php

namespace Drupal\contexts\EventSubscriber;


use Drupal\contexts\Service\ContextsManagerInterface;
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
   * ContextsRequestSubscriber constructor.
   *
   * @param ContextsManagerInterface $contextsManager
   *   Contexts manager.
   */
  public function __construct(ContextsManagerInterface $contextsManager) {

    $this->contextsManager = $contextsManager;
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
      $this->contextsManager->negotiateContexts($uri);
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {

    $events[KernelEvents::REQUEST][] = ['onKernelRequestContexts', 255];

    return $events;
  }
}