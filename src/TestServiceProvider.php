<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 16.02.16
 * Time: 16:03
 */

namespace Lebran;

use Lebran\Container\ServiceProviderInterface;

class TestServiceProvider implements ServiceProviderInterface
{

    /**
     * Lebran\Container service provider interface.
     *
     * This method should only be used to configure services and parameters.
     *
     * @param Container $di The container instance.
     *
     * @return void
     */
    public function register(Container $di)
    {
        $di->set('test', new TestService());
    }
}