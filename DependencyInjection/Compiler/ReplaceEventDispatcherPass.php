<?php

namespace Jmikola\WildcardEventDispatcherBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class ReplaceEventDispatcherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setDefinition('event_dispatcher', $container->getDefinition('jmikola_wildcard_event_dispatcher.event_dispatcher'));
        $container->removeDefinition('jmikola_wildcard_event_dispatcher.event_dispatcher');
    }
}
