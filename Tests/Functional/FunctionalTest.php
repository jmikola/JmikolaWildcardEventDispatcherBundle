<?php

namespace Jmikola\WildcardEventDispatcherBundle\Tests\Functional;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FunctionalTest extends WebTestCase
{
    public function testDispatch()
    {
        $client = $this->createClient();

        $listener = $client->getContainer()->get('count_listener');
        $this->assertEquals(0, $listener->getNumEventsReceived());

        try {
            $client->request('GET', '/');
        } catch (NotFoundHttpException $e) {}

        $this->assertEquals(1, $listener->getNumEventsReceived());
    }
}
