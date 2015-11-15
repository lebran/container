<?php
namespace Lebran\Container;

use Lebran\Container;

/**
 * Interface ServiceProviderInterface
 *
 * @package Lebran\Di
 */
interface ServiceProviderInterface
{
    /**
     * @param Container $di
     *
     * @return mixed
     */
    public function register(Container $di);
}