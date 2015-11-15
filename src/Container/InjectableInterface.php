<?php
namespace Lebran\Container;

use Lebran\Container;

/**
 * This interface must be implemented in those classes that need internal dependency injection container.
 *
 * @package    Di
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
interface InjectableInterface
{
    /**
     * Sets the dependency injection container.
     *
     * @param Container $di Container object.
     *
     * @return void
     */
    public function setDi(Container $di);

    /**
     * Returns the dependency injection container.
     *
     * @return Container object.
     */
    public function getDi();
}