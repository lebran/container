<?php
namespace Lebran\Container;

use Lebran\Container;

/**
 * It's trait help you realize InjectableInterface.
 *
 * @package    Container
 * @version    1.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    MIT
 * @copyright  2015 - 2016 Roman Kritskiy
 */
interface ServiceProviderInterface
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
    public function register(Container $di);
}