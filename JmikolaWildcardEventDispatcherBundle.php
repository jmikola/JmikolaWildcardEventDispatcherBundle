<?php

namespace Jmikola\WildcardEventDispatcherBundle;

use Jmikola\WildcardEventDispatcherBundle\DependencyInjection\Compiler\EventDispatcherPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JmikolaWildcardEventDispatcherBundle extends Bundle
{
    /**
     * @see Symfony\Component\HttpKernel\Bundle\Bundle::build()
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EventDispatcherPass());
    }
}
