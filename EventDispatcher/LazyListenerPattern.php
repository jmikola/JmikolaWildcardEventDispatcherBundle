<?php

namespace Jmikola\EventWildcardBundle\EventDispatcher;

class LazyListenerPattern extends ListenerPattern
{
    protected $listenerProvider;

    /**
     * Constructor.
     *
     * The $listenerProvider argument should be a callback which, when invoked,
     * returns the listener callback.
     *
     * @param string   $eventPattern
     * @param callback $listenerProvider
     * @param integer  $priority
     * @throws InvalidArgumentException if the listener provider is not a callback
     */
    public function __construct($eventPattern, $listenerProvider, $priority = 0)
    {
        if (!is_callable($listenerProvider)) {
            throw new \InvalidArgumentException('Listener provider argument must be a callback');
        }

        parent::__construct($eventPattern, null, $priority);

        $this->listenerProvider = $listenerProvider;
    }

    /**
     * Get the pattern listener, initializing it lazily from its provider.
     *
     * @return callback
     */
    public function getListener()
    {
        if (!isset($this->listener) && isset($this->listenerProvider)) {
            $this->listener = call_user_func($this->listenerProvider);
            unset($this->listenerProvider);
        }

        return $this->listener;
    }
}
