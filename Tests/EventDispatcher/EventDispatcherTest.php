<?php

namespace Jmikola\EventWildcardBundle\Tests\EventDispatcher;

use Jmikola\EventWildcardBundle\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    private $dispatcher;
    private $innerDispatcher;

    public function setUp()
    {
        $this->innerDispatcher = $this->getMockEventDispatcher();
        $this->dispatcher = new EventDispatcher($this->innerDispatcher);
    }

    /**
     * @dataProvider provideListenersWithoutWildcards
     */
    public function testShouldAddListenersWithoutWildcardsEagerly($eventName, $listener, $priority)
    {
        $this->innerDispatcher->expects($this->once())
            ->method('addListener')
            ->with($eventName, $listener, $priority);

        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    public function provideListenersWithoutWildcards()
    {
        return array(
            array('core.request', 'callback', 0),
            array('core.exception', array('class', 'method'), 5),
        );
    }

    /**
     * @dataProvider provideListenersWithWildcards
     */
    public function testShouldAddListenersWithWildcardsLazily($eventName, $listener, $priority)
    {
        $this->innerDispatcher->expects($this->never())
            ->method('addListener');

        $this->dispatcher->addListener($eventName, $listener, $priority);
    }

    public function provideListenersWithWildcards()
    {
        return array(
            array('core.*', 'callback', 0),
            array('#', array('class', 'method'), -10),
        );
    }

    public function testShouldAddListenersWithWildcardsWhenMatchingEventIsDispatched()
    {
        $this->innerDispatcher->expects($this->once())
            ->id('listener-is-added')
            ->method('addListener')
            ->with('core.request', 'callback', 0);

        $this->innerDispatcher->expects($this->once())
            ->after('listener-is-added')
            ->method('dispatch')
            ->with('core.request');

        $this->dispatcher->addListener('core.*', 'callback', 0);
        $this->dispatcher->dispatch('core.request');
    }

    public function testShouldAddListenersWithWildcardsWhenListenersForMatchingEventsAreRetrieved()
    {
        $this->innerDispatcher->expects($this->once())
            ->id('listener-is-added')
            ->method('addListener')
            ->with('core.request', 'callback', 0);

        $this->innerDispatcher->expects($this->once())
            ->after('listener-is-added')
            ->method('getListeners')
            ->with('core.request')
            ->will($this->returnValue(array('callback')));

        $this->dispatcher->addListener('core.*', 'callback', 0);

        $this->assertEquals(array('callback'), $this->dispatcher->getListeners('core.request'));
    }

    public function testShouldNotCountWildcardListenersThatHaveNeverBeenMatchedWhenAllListenersAreRetrieved()
    {
        /* When getListeners() is called without an event name, it attempts to
         * return the collection of listeners for all events it knows about.
         * When working with wildcards, we cannot anticipate events until we
         * encounter a matching name. Therefore, getListeners() will ignore any
         * wildcard listeners that are registered but haven't matched anything.
         */
        $this->innerDispatcher->expects($this->never())
            ->method('addListener');

        $this->innerDispatcher->expects($this->any())
            ->method('getListeners')
            ->will($this->returnValue(array()));

        $this->dispatcher->addListener('core.*', 'callback', 0);
        $this->assertEquals(array(), $this->dispatcher->getListeners());
    }

    public function testAddingAndRemovingAnEventSubscriber()
    {
        /* Since the EventSubscriberInterface defines getSubscribedEvents() as
         * static, we cannot mock it with PHPUnit and must use a stub class.
         */
        $subscriber = new TestEventSubscriber();

        $i = 0;
        $priority = 10;
        $numSubscribedEvents = count($subscriber->getSubscribedEvents());

        foreach ($subscriber->getSubscribedEvents() as $eventName => $method) {
            $this->innerDispatcher->expects($this->at($i))
                ->method('addListener')
                ->with($eventName, array($subscriber, $method), $priority);

            $this->innerDispatcher->expects($this->at($numSubscribedEvents + $i))
                ->method('removeListener')
                ->with($eventName, array($subscriber, $method));

            ++$i;
        }

        $this->dispatcher->addSubscriber($subscriber, $priority);
        $this->dispatcher->removeSubscriber($subscriber, $priority);
    }

    private function getMockEventDispatcher()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }
}

class TestEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array('core.request' => 'onRequest', 'core.exception' => 'onException');
    }
}
