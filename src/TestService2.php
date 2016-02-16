<?php
/**
 * Created by PhpStorm.
 * User: mindkicker
 * Date: 16.02.16
 * Time: 16:10
 */

namespace Lebran;

class TestService2
{
    public $param;

    public $param1;

    public $param2;

    public $param3;

    public function __construct(TestService $param, $param1, $param2 = 'test3', $param3)
    {
        $this->param = $param;
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }
}