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
trait InjectableTrait
{
    /**
     * @var Container Store for di container.
     */
    protected $di;

    /**
     * Sets the dependency injection container.
     *
     * @param Container $di Container object.
     *
     * @return $this
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