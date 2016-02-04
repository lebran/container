<?php
namespace Lebran\Container;

use Lebran\Container;

/**
 * This interface must be implemented in those classes that need internal dependency injection container.
 *
 * @package    Container
 * @version    1.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    MIT
 * @copyright  2015 - 2016 Roman Kritskiy
 */
interface InjectableInterface
{
    /**
     * Sets the dependency injection container.
     *
     * @param Container $di Container object.
     *
     * @return $this
     */
    public function setDi(Container $di);

    /**
     * Returns the dependency injection container.
     *
     * @return Container object.
     */
    public function getDi();
}