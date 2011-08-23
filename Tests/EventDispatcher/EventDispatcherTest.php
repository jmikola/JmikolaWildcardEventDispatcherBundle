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

    /**
     * @dataProvider provideListenersWithWildcards
     */
    public function testShouldAddListenersWithWildcardsLazily2($eventName, $listener, $priority)
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
