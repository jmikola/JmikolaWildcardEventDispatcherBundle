<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\DependencyInjection;

use Jmikola\EventWildcardBundle\DependencyInjection\JmikolaEventWildcardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class JmikolaEventWildcardExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldReplaceOriginalEventDispatcher()
    {
        $container = new ContainerBuilder();
        $loader = new JmikolaEventWildcardExtension();
        $originalDefinition = new Definition();

        $container->setDefinition('event_dispatcher', $originalDefinition);

        $loader->load(array(array()), $container);

        $this->assertSame($originalDefinition, $container->getDefinition('jmikola_event_wildcard.event_dispatcher.original'), 'The original event dispatcher definition was preserved with a different service ID');
        $this->assertEquals('event_dispatcher', (string) $container->getAlias('jmikola_event_wildcard.event_dispatcher'), 'The event dispatcher maintains the service ID from event_dispatcher.xml as an alias');
        $definition = $container->getDefinition('event_dispatcher');
        $this->assertEquals('jmikola_event_wildcard.event_dispatcher.original', (string) $definition->getArgument(1), 'The event dispatcher references the original event dispatcher for its second constructor argument');
    }
}
