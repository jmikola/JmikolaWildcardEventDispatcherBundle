<?php

namespace Jmikola\WildcardEventDispatcherBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    static protected function getKernelClass()
    {
        require_once __DIR__ . '/app/AppKernel.php';

        return __NAMESPACE__ . '\AppKernel';
    }
}
