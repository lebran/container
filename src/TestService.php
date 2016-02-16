<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 16.02.16
 * Time: 12:41
 */

namespace Lebran;

use Lebran\Container\InjectableInterface;
use Lebran\Container\InjectableTrait;

class TestService implements InjectableInterface
{
    use InjectableTrait;

    public function testCall(TestServiceProvider $param1){
        return $param1;
    }
}