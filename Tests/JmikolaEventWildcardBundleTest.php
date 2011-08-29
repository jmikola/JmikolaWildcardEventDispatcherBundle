<?php

namespace Jmikola\EventWildcardBundle\Tests;

use Jmikola\EventWildcardBundle\JmikolaEventWildcardBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class JmikolaEventWildcardBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldAddCompilerPass()
    {
        $bundle = new JmikolaEventWildcardBundle();
        $container = new ContainerBuilder();

        $bundle->build($container);

        $passes = $container->getCompilerPassConfig()->getBeforeOptimizationPasses();
        $this->assertInstanceOf('Jmikola\EventWildcardBundle\DependencyInjection\Compiler\EventDispatcherPass', $passes[0]);
    }
}
