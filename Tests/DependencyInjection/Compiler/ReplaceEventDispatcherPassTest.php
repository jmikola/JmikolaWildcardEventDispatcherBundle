<?php

namespace Jmikola\WildcardEventDispatcherBundle\Tests\DependencyInjection\Compiler;

use Jmikola\WildcardEventDispatcherBundle\DependencyInjection\Compiler\ReplaceEventDispatcherPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ReplaceEventDispatcherPassTest extends TestCase
{
    private $container;
    private $pass;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->pass = new ReplaceEventDispatcherPass();
    }

    public function testShouldReplaceDefinition()
    {
        $definition = new Definition();
        $this->container->setDefinition('jmikola_wildcard_event_dispatcher.event_dispatcher', $definition);

        $this->pass->process($this->container);

        $this->assertSame($definition, $this->container->getDefinition('event_dispatcher'));
        $this->assertFalse($this->container->hasDefinition('jmikola_wildcard_event_dispatcher.event_dispatcher'));
    }
}
