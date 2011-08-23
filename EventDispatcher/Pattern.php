<?php

namespace Jmikola\EventWildcardBundle\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Pattern
{
    private $eventPattern;
    private $events = array();
    private $listener;
    private $priority;
    private $regex;

    private static $replacements = array(
        // Trailing single-wildcard with separator prefix
        '/\\\\\.\\\\\*$/'     => '(?:\.\w+)?',
        // Single-wildcard with separator prefix
        '/\\\\\.\\\\\*/'      => '(?:\.\w+)',
        // Single-wildcard without separator prefix
        '/(?<!\\\\\.)\\\\\*/' => '(?:\w+)',
        // Multi-wildcard with separator prefix
        '/\\\\\.#/'           => '(?:\.\w+)*',
        // Multi-wildcard without separator prefix
        '/(?<!\\\\\.)#/'      => '(?:|\w+(?:\.\w+)*)',
    );

    /**
     * Constructor.
     *
     * @param string   $eventPattern
     * @param callback $listener
     * @param integer  $priority
     * 
     */
    public function __construct($eventPattern, $listener, $priority = 0)
    {
        $this->eventPattern = $eventPattern;
        $this->listener = $listener;
        $this->priority = $priority;
        $this->regex = $this->createRegex($eventPattern);
    }

    /**
     * Get the pattern listener.
     *
     * @return callback
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     * Adds this pattern's listener to an event.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param string                   $eventName
     * @throw InvalidArgumentException
     */
    public function bind(EventDispatcherInterface $dispatcher, $eventName)
    {
        if (isset($this->events[$eventName])) {
            return;
        }

        if (!$this->test($eventName)) {
            throw new \InvalidArgumentException(sprintf('Pattern "%s" does not match event name "%s"', $this->eventPattern, $eventName));
        }

        $dispatcher->addListener($eventName, $this->listener, $this->priority);
        $this->events[$eventName] = true;
    }

    /**
     * Removes this pattern's listener from all events to which it was
     * previously added.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    public function unbind(EventDispatcherInterface $dispatcher)
    {
        foreach ($this->events as $eventName => $_) {
            $dispatcher->removeListener($eventName, $this->listener);
        }

        $this->events = array();
    }

    /**
     * Tests if this pattern matches and event name.
     *
     * @param string $eventName
     * @return boolean
     */
    public function test($eventName)
    {
        return (boolean) preg_match($this->regex, $eventName);
    }

    /**
     * Transforms an event pattern into a regular expression.
     *
     * @param string $eventPattern
     * @return string
     */
    private function createRegex($eventPattern)
    {
        return sprintf('/^%s$/', preg_replace(
            array_keys(self::$replacements),
            array_values(self::$replacements),
            preg_quote($eventPattern, '/')
        ));
    }
}