<?php

namespace Drupal\varbase_api_vmp\EventSubscriber;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\jsonapi\Routing\Routes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class VmpResponseSubscriber to alter JsonApi response.
 */
class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE] = ['onResponse'];

    return $events;
  }

  /**
   * Set route match service.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The  route match service.
   */
  public function setRouteMatch(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * This method is called the KernelEvents::RESPONSE event is dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The filter event.
   */
  public function onResponse(FilterResponseEvent $event) {

    if (!$this->routeMatch->getRouteObject()) {
      return;
    }

    $route = $this->routeMatch->getRouteObject();
    $route_options = $route->getOptions();

    if (!empty($route->getDefaults()[Routes::JSON_API_ROUTE_FLAG_KEY]) && isset($route_options['parameters']['entity']) && $route_options['parameters']['entity']['type'] == "entity:node") {
      $content = $event->getResponse()->getContent();
      if (strpos($content, '{"jsonapi"') === 0) {
        $content_decoded = Json::decode($content);
        $entity = $this->routeMatch->getParameter('entity');
        $content_decoded['data']['links']['website']['href'] = $entity->toUrl('canonical', ['absolute' => TRUE])->toString();
        $content = Json::encode($content_decoded);
        $event->getResponse()->setContent($content);
      }
    }
  }

}
