<?php
namespace Lebran\Container;

use Exception;
use Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;

/**
 * No entry was found in the container.
 *
 * @package    Container
 * @version    1.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    MIT
 * @copyright  2015 - 2016 Roman Kritskiy
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}