<?php

namespace Jmikola\EventWildcardBundle\Tests\Functional;

use Jmikola\EventWildcardBundle\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher as SymfonyContainerAwareEventDispatcher;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\Event;

class ContainerAwareEventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    const coreRequest = 'core.request';
    const coreException = 'core.exception';
    const testListenerServiceId = 'test.listener';

    private $container;
    private $dispatcher;
    private $innerDispatcher;
    private $listener;

    public function setUp()
    {
        $this->container = new Container();
        $this->innerDispatcher = new SymfonyContainerAwareEventDispatcher($this->container);
        $this->dispatcher = new ContainerAwareEventDispatcher($this->container, $this->innerDispatcher);
        $this->listener = new TestEventListenerService();

        $this->container->set(self::testListenerServiceId, $this->listener);
    }

    public function testInitialState()
    {
        $this->assertTrue($this->container->has(self::testListenerServiceId));
        $this->assertEquals(array(), $this->dispatcher->getListeners());
        $this->assertFalse($this->dispatcher->hasListeners(self::coreRequest));
        $this->assertFalse($this->dispatcher->hasListeners(self::coreException));
    }

    public function testAddingAndRemovingListenerServices()
    {
        $this->dispatcher->addListenerService('core.*', array(self::testListenerServiceId, 'onCore'));

        /* Symfony's ContainerAwareEventDispatcher does not add its listener
         * services until dispatch() is called. That is a bit lazier than our
         * pattern initialization, which will bind if either dispatch() or
         * getListeners() is called.
         */
        $this->assertNumberListenersAdded(1, self::coreRequest);
        $this->assertNumberListenersAdded(1, self::coreException);
        $this->assertNumberListenersAdded(2);

        $this->dispatcher->removeListener('core.*', array($this->listener, 'onCore'));

        $this->assertNumberListenersAdded(0, self::coreRequest);
        $this->assertNumberListenersAdded(0, self::coreException);
        $this->assertNumberListenersAdded(0);
    }

    public function testAddedListenerServicesWithWildcardsAreRegisteredLazily()
    {
        $this->dispatcher->addListenerService('core.*', array(self::testListenerServiceId, 'onCore'));

        $this->assertNumberListenersAdded(0);

        $this->assertTrue($this->dispatcher->hasListeners(self::coreRequest));
        $this->assertNumberListenersAdded(1, self::coreRequest);
        $this->assertNumberListenersAdded(1);

        $this->assertTrue($this->dispatcher->hasListeners(self::coreException));
        $this->assertNumberListenersAdded(1, self::coreException);
        $this->assertNumberListenersAdded(2);
    }

    public function testDispatch()
    {
        $this->dispatcher->addListenerService('core.*', array(self::testListenerServiceId, 'onCore'));
        $this->dispatcher->addListenerService(self::coreRequest, array(self::testListenerServiceId, 'onCoreRequest'));

        $this->dispatcher->dispatch(self::coreRequest);
        $this->dispatcher->dispatch(self::coreException);

        $this->assertEquals(2, $this->listener->onCoreInvoked);
        $this->assertEquals(1, $this->listener->onCoreRequestInvoked);
    }

    /**
     * Asserts the number of listeners added for a specific event or all events
     * in total.
     *
     * @param integer $expected
     * @param string  $eventName
     */
    private function assertNumberListenersAdded($expected, $eventName = null)
    {
        return isset($eventName)
            ? $this->assertEquals($expected, count($this->dispatcher->getListeners($eventName)))
            : $this->assertEquals($expected, array_sum(array_map('count', $this->dispatcher->getListeners())));
    }
}

class TestEventListenerService
{
    public $onCoreInvoked = 0;
    public $onCoreRequestInvoked = 0;

    public function onCore(Event $e)
    {
        ++$this->onCoreInvoked;
    }

    public function onCoreRequest(Event $e)
    {
        ++$this->onCoreRequestInvoked;
    }
}
