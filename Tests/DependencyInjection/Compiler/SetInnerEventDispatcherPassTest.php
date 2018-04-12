<?php

namespace Jmikola\WildcardEventDispatcherBundle\Tests\DependencyInjection\Compiler;

use Jmikola\WildcardEventDispatcherBundle\DependencyInjection\Compiler\SetInnerEventDispatcherPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class SetInnerEventDispatcherPassTest extends TestCase
{
    private $container;
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->pass = new SetInnerEventDispatcherPass();
    }

    public function testShouldComposeAlias()
    {
        $this->container->setDefinition('event_dispatcher.real', new Definition());
        $this->container->setAlias('event_dispatcher', 'event_dispatcher.real');

        $this->pass->process($this->container);

        $this->assertServiceHasAlias('event_dispatcher.real', 'jmikola_wildcard_event_dispatcher.event_dispatcher.inner');
        $this->assertFalse($this->container->getAlias('jmikola_wildcard_event_dispatcher.event_dispatcher.inner')->isPublic());
    }

    public function testShouldComposeDefinition()
    {
        $this->container->setDefinition('event_dispatcher', $originalDefinition = new Definition());

        $this->pass->process($this->container);

        $newDefinition = $this->container->getDefinition('jmikola_wildcard_event_dispatcher.event_dispatcher.inner');
        $this->assertFalse($newDefinition->isPublic());
        $this->assertSame($originalDefinition, $newDefinition);
    }

    private function assertServiceHasAlias($serviceId, $aliasId)
    {
        $this->assertEquals($serviceId, (string) $this->container->getAlias($aliasId), sprintf('Service "%s" has alias "%s"', $serviceId, $aliasId));
    }
}
