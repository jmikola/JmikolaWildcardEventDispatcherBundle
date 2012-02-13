<?php

namespace Jmikola\WildcardEventDispatcherBundle\EventDispatcher;

use Jmikola\WildcardEventDispatcher\EventDispatcher;
use Jmikola\WildcardEventDispatcher\LazyListenerPattern;
use Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher as BaseContainerAwareEventDispatcher;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\Event;

class ContainerAwareEventDispatcher extends EventDispatcher
{
    private $container;
    private $dispatcher;

    /**
     * Constructor.
     *
     * @param ContainerInterface                $container
     * @param BaseContainerAwareEventDispatcher dispatcher
     */
    public function __construct(ContainerInterface $container, BaseContainerAwareEventDispatcher $dispatcher)
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
}
