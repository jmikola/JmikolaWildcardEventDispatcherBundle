<?php

namespace Jmikola\EventWildcardBundle\Tests\EventDispatcher;

use Jmikola\EventWildcardBundle\EventDispatcher\LazyListenerPattern;

class LazyListenerPatternTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorRequiresListenerProviderCallback()
    {
        new LazyListenerPattern('*', null);
    }

    public function testLazyListenerInitialization()
    {
        $listenerProviderInvoked = 0;

        $listenerProvider = function() use (&$listenerProviderInvoked) {
            ++$listenerProviderInvoked;
            return 'callback';
        };

        $pattern = new LazyListenerPattern('*', $listenerProvider);

        $this->assertEquals(0, $listenerProviderInvoked, 'The listener provider should not be invoked until the listener is requested');
        $this->assertEquals('callback', $pattern->getListener());
        $this->assertEquals(1, $listenerProviderInvoked, 'The listener provider should be invoked when the listener is requested');
        $this->assertEquals('callback', $pattern->getListener());
        $this->assertEquals(1, $listenerProviderInvoked, 'The listener provider should only be invoked once');
    }
}
