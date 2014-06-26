<?php

namespace Jmikola\WildcardEventDispatcherBundle\EventDispatcher;

use Jmikola\WildcardEventDispatcher\WildcardEventDispatcher;
use Jmikola\WildcardEventDispatcher\LazyListenerPattern;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContainerAwareEventDispatcher extends WildcardEventDispatcher
{
    private $container;
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param ContainerInterface       $container
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ContainerInterface $container, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($dispatcher);

        $this->container = $container;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @see BaseContainerAwareEventDispatcher::hasListeners
     */
    public function addListenerService($eventName, $callback, $priority = 0)
    {
        return $this->hasWildcards($eventName)
            ? $this->addListenerServicePattern($eventName, $callback, $priority)
            : $this->dispatcher->addListenerService($eventName, $callback, $priority);
    }

    /**
     * Adds a service as an event listener for all events matching the specified
     * pattern.
     *
     * @param string   $eventPattern
     * @param callback $callback
     * @param integer  $priority
     * @throw InvalidArgumentException if the callback is not a service/method tuple
     */
    private function addListenerServicePattern($eventPattern, $callback, $priority = 0)
    {
        if (!is_array($callback) || 2 !== count($callback)) {
            throw new \InvalidArgumentException('Expected an array("service", "method") argument');
        }

        $container = $this->container;
        $listenerProvider = function() use ($container, $callback) {
            return array($container->get($callback[0]), $callback[1]);
        };

        $this->addListenerPattern(new LazyListenerPattern($eventPattern, $listenerProvider, $priority));
    }

    /**
     * Adds a service as an event subscriber for all events matching the
     * specified pattern
     *
     * @param string $serviceId The service ID of the subscriber service
     * @param string $class     The service's class name (which must implement EventSubscriberInterface)
     */
    public function addSubscriberService($serviceId, $class)
    {
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListenerService($eventName, array($serviceId, $params), 0);
            } elseif (is_string($params[0])) {
                $this->addListenerService($eventName, array($serviceId, $params[0]), isset($params[1]) ? $params[1] : 0);
            } else {
                foreach ($params as $listener) {
                    $this->addListenerService($eventName, array($serviceId, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
                }
            }
        }
    }
}
