<?php

namespace Jmikola\WildcardEventDispatcherBundle;

use Jmikola\WildcardEventDispatcherBundle\DependencyInjection\Compiler\EventDispatcherPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JmikolaWildcardEventDispatcherBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        // Ensure execution after FrameworkBundle's RegisterKernelListenersPass
        $container->addCompilerPass(new EventDispatcherPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
