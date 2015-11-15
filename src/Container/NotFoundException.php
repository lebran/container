<?php
namespace Lebran\Container;

use Exception;
use Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;

/**
 * Class NotFoundException
 *
 * @package Lebran\Di\Exception
 */
class NotFoundException extends Exception implements NotFoundExceptionInterface
{
}