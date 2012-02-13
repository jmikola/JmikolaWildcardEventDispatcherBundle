<?php

namespace Jmikola\WildcardEventDispatcherBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class EventDispatcherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /* Copy the alias or definition of the original event dispatcher that
         * this bundle's event dispatcher will compose, and then assume the
         * "event_dispatcher" service ID.
         */
        if ($container->hasAlias('event_dispatcher')) {
            $container->setAlias('jmikola_wildcard_event_dispatcher.event_dispatcher.inner', new Alias((string) $container->getAlias('event_dispatcher'), false));
        } else {
            $definition = $container->getDefinition('event_dispatcher');
            $definition->setPublic(false);
            $container->setDefinition('jmikola_wildcard_event_dispatcher.event_dispatcher.inner', $definition);
        }

        $container->setAlias('event_dispatcher', 'jmikola_wildcard_event_dispatcher.event_dispatcher');
    }
}
