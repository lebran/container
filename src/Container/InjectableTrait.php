<?php
namespace Lebran\Container;

use Lebran\Container;

/**
 * It's trait help you realize InjectableInterface.
 *
 * @package    Di
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
trait InjectableTrait
{
    /**
     * Store for di container.
     *
     * @var
     */
    protected $di;

    /**
     * Sets the dependency injection container.
     *
     * @param Container $di Container object.
     *
     * @return self
     */
    public function setDi(Container $di)
    {
        $this->di = $di;
        return $this;
    }

    /**
     * Returns the dependency injection container.
     *
     * @return Container object.
     */
    public function getDi()
    {
        return $this->di;
    }
}