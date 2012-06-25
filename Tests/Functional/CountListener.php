<?php

namespace Jmikola\WildcardEventDispatcherBundle\Tests\Functional;

class CountListener
{
    private $numEventsReceived = 0;

    public function onEvent()
    {
        ++$this->numEventsReceived;
    }

    public function getNumEventsReceived()
    {
        return $this->numEventsReceived;
    }
}
