<?php
namespace Lebran;

use Exception;
use Interop\Container\Exception\ContainerException as ContainerExceptionInterface;

/**
 * Base representing a generic exception in a container.
 *
 * @package    Container
 * @version    1.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    MIT
 * @copyright  2015 - 2016 Roman Kritskiy
 */
class ContainerException extends Exception implements ContainerExceptionInterface
{
}