<?php

namespace Jmikola\EventWildcardBundle\Tests\DependencyInjection;

use Jmikola\EventWildcardBundle\DependencyInjection\JmikolaEventWildcardExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JmikolaEventWildcardExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldLoadServiceDefinition()
    {
        $container = new ContainerBuilder();
        $loader = new JmikolaEventWildcardExtension();

        $loader->load(array(array()), $container);

        $this->assertTrue($container->hasDefinition('jmikola_event_wildcard.event_dispatcher'));
    }
}
