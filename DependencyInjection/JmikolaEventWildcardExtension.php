<?php

namespace Jmikola\EventWildcardBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class JmikolaEventWildcardExtension extends Extension
{
    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::load()
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('event_dispatcher.xml');

        /* The original event dispatcher (at the time this extension is loaded)
         * will be saved under an alternate service ID. This bundle's event
         * dispatcher will assume the "event_dispatcher" ID and incorporate the
         * original as a constructor argument.
         *
         * Note: if this leads to a race condition (i.e. if FrameworkExtension
         * is loaded after this extension), consider replacing this logic with
         * an early compiler pass.
         */
        $container->setDefinition('jmikola_event_wildcard.event_dispatcher.original', $container->findDefinition('event_dispatcher'));
        $container->setDefinition('event_dispatcher', $definition = $container->findDefinition('jmikola_event_wildcard.event_dispatcher'));
        $container->setAlias('jmikola_event_wildcard.event_dispatcher', 'event_dispatcher');

        $definition->replaceArgument(1, new Reference('jmikola_event_wildcard.event_dispatcher.original'));
    }

    /**
     * @see Symfony\Component\DependencyInjection\Extension\ExtensionInterface::getAlias()
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return 'jmikola_event_wildcard';
    }
}
