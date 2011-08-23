<?php

namespace Jmikola\EventWildcardBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatcher implements EventDispatcherInterface
{
    private $dispatcher;
    private $patterns = array();
    private $syncedEvents = array();

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @see EventDispatcherInterface::dispatch
     */
    public function dispatch($eventName, Event $event = null)
    {
        $this->bindPatterns($eventName);

        return $this->dispatcher->dispatch($eventName, $event);
    }

    /**
     * @see EventDispatcherInterface::getListeners
     */
    public function getListeners($eventName = null)
    {
        if (null !== $eventName) {
            $this->bindPatterns($eventName);

            return $this->dispatcher->getListeners($eventName);
        }

        /* Ensure that any patterns matching a known event name are bound. If
         * we don't this this, it's possible that getListeners() could return
         * different values due to lazy listener registration.
         */
        foreach (array_keys($this->dispatcher->getListeners()) as $eventName) {
            $this->bindPatterns($eventName);
        }

        return $this->dispatcher->getListeners();
    }

    /**
     * @see EventDispatcherInterface::hasListeners
     */
    public function hasListeners($eventName = null)
    {
        return (boolean) count($this->getListeners($eventName));
    }

    /**
     * @see EventDispatcherInterface::addListener
     */
    public function addListener($eventName, $listener, $priority = 0)
    {
        return $this->hasWildcards($eventName)
            ? $this->addPatternListener($eventName, $listener, $priority)
            : $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    /**
     * @see EventDispatcherInterface::removeListener
     */
    public function removeListener($eventName, $listener)
    {
        return $this->hasWildcards($eventName)
            ? $this->removePatternListener($eventName, $listener)
            : $this->dispatcher->removeListener($eventName, $listener);
    }

    /**
     * @see EventDispatcherInterface::addSubscriber
     */
    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            if (is_array($params)) {
                $this->addListener($eventName, array($subscriber, $params[0]), $params[1]);
            } else {
                $this->addListener($eventName, array($subscriber, $params));
            }
        }
    }

    /**
     * @see EventDispatcherInterface::removeSubscriber
     */
    public function removeSubscriber(EventSubscriberInterface $subscriber)
    {
        foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
            $this->removeListener($eventName, array($subscriber, is_array($params) ? $params[0] : $params));
        }
    }

    /**
     * Checks whether a string contains any wildcard characters.
     *
     * @param string $subject
     * @return boolean
     */
    private function hasWildcards($subject)
    {
        return false !== strpos($subject, '*') || false !== strpos($subject, '#');
    }

    /**
     * Binds all patterns that match the specified event name.
     *
     * @param string $eventName
     */
    private function bindPatterns($eventName)
    {
        if (isset($this->syncedEvents[$eventName])) {
            return;
        }

        foreach ($this->patterns as $eventPattern => $patterns) {
            foreach ($patterns as $pattern) {
                if ($pattern->test($eventName)) {
                    $pattern->bind($this->dispatcher, $eventName);
                }
            }
        }

        $this->syncedEvents[$eventName] = true;
    }

    /**
     * Adds an event listener for all events matching the specified pattern.
     *
     * This method will lazily register the listener when a matching event is
     * dispatched.
     *
     * @param string   $eventPattern
     * @param callback $listener
     * @param integer  $priority
     */
    private function addPatternListener($eventPattern, $listener, $priority = 0)
    {
        $pattern = new Pattern($eventPattern, $listener, $priority);
        $this->patterns[$eventPattern][] = $pattern;

        foreach ($this->syncedEvents as $eventName => $_) {
            if ($pattern->test($eventName)) {
                unset($this->syncedEvents[$eventName]);
            }
        }
    }

    /**
     * Removes an event listener from any events to which it was applied due to
     * pattern matching.
     *
     * This method cannot be used to remove a listener from a pattern that was
     * never registered.
     *
     * @param string   $eventPattern
     * @param callback $listener
     */
    private function removePatternListener($eventPattern, $listener)
    {
        if (!isset($this->patterns[$eventPattern])) {
            return;
        }

        foreach ($this->patterns[$eventPattern] as $key => $pattern) {
            if ($listener == $pattern->getListener()) {
                $pattern->unbind($this->dispatcher);
                unset($this->patterns[$eventPattern][$key]);
            }
        }
    }
}
