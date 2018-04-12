<?php

namespace Jmikola\WildcardEventDispatcherBundle\Tests\DependencyInjection;

use Jmikola\WildcardEventDispatcherBundle\DependencyInjection\JmikolaWildcardEventDispatcherExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JmikolaWildcardEventDispatcherExtensionTest extends TestCase
{
    public function testShouldLoadServiceDefinition()
    {
        $container = new ContainerBuilder();
        $loader = new JmikolaWildcardEventDispatcherExtension();

        $loader->load(array(array()), $container);

        $this->assertTrue($container->hasDefinition('jmikola_wildcard_event_dispatcher.event_dispatcher'));
    }
}
