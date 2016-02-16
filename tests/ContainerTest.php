<?php
namespace Lebran;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testAllDefinitions($definition)
    {
        $di = new Container();
        $di->set('test', $definition);
        $this->assertInstanceOf('Lebran\TestService', $di->get('test'));
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testInjectable($definition)
    {
        $di = new Container();
        $di->set('test', $definition);
        $service = $di->get('test');
        $this->assertInstanceOf('Lebran\Container\InjectableInterface', $service);
        $this->assertSame($di, $service->getDi());
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testServicesShouldBeDifferent($definition)
    {
        $di = new Container();
        $di->set('test', $definition);
        $this->assertNotSame($di->get('test'), $di->get('test'));
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testSharedServices($definition)
    {
        $di = new Container();
        $di->shared('test', $definition);
        $this->assertSame($di->get('test'), $di->get('test'));
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testIsSharedAndSetShared($definition)
    {
        $di = new Container();
        $di->shared('test', $definition);

        $this->assertTrue($di->isShared('test'));
        $di->setShared('test', false);
        $this->assertFalse($di->isShared('test'));
    }

    /**
     * @expectedException \Lebran\Container\NotFoundException
     */
    public function testSetSharedNotFound()
    {
        $di = new Container();
        $di->setShared('test');
    }


    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testHasAndRemove($definition)
    {
        $di = new Container();
        $di->set('test', $definition);

        $this->assertTrue($di->has('test'));
        $di->remove('test');
        $this->assertFalse($di->has('test'));
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testMagicalSyntax($definition)
    {
        $di = new Container();
        $di->test = $definition;
        $this->assertTrue(isset($di->test));
        $this->assertInstanceOf('Lebran\TestService', $di->test);
        $this->assertSame($di->test, $di->test);
        unset($di->test);
        $this->assertFalse(isset($di->test));
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testArrayAccessSyntax($definition)
    {
        $di = new Container();
        $di['test'] = $definition;
        $this->assertTrue(isset($di['test']));
        $this->assertInstanceOf('Lebran\TestService', $di['test']);
        $this->assertNotSame($di['test'], $di['test']);
        unset($di['test']);
        $this->assertFalse(isset($di['test']));
    }

    public function providerDefinitions()
    {
        return [
            ['Lebran\TestService'],
            [new TestService()],
            [function(){
                return new TestService();
            }]
        ];
    }

    public function testStaticInstance()
    {
        $di = new Container();
        $di1 = new Container();
        $this->assertNotSame($di, Container::instance());
        $this->assertSame($di1, Container::instance());
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerDefinitions
     */
    public function testNormalize($definition)
    {
        $di = new Container();
        $di->set('test', $definition);
        $this->assertInstanceOf('Lebran\TestService', $di->get('test'));
    }

    public function providerNormalize()
    {
        return [
            ['Lebran\TestService'],
            ['\Lebran\TestService\\'],
            ['                   Lebran\TestService       '],
            ['       \\\\\\Lebran\TestService'],
            ['Lebran\TestService\\\\\\\            ']
        ];
    }

    /**
     * @expectedException \Lebran\ContainerException
     */
    public function testCircle()
    {
        $di = new Container();
        $di->set('Lebran\TestService', 'Lebran\TestService')
            ->get('Lebran\TestService');
    }

    /**
     * @expectedException \Lebran\ContainerException
     */
    public function testCircle2()
    {
        $di = new Container();

        $di->set('Lebran\TestService', 'test')
           ->set('test', 'Lebran\TestService')
           ->get('Lebran\TestService');
    }


    public function testStringDefinition()
    {
        $di = new Container();
        $this->assertInstanceOf('Lebran\TestService', $di->get('Lebran\TestService'));
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerWrongDefinitions
     * @expectedException \Lebran\ContainerException
     */
    public function testWrongDefinition($definition)
    {
        $di = new Container();
        $di->set('test', $definition)
           ->get('test');
    }

    public function providerWrongDefinitions()
    {
        return [
            [[]],
            [10],
            [10.5]
        ];
    }

    /**
     * @expectedException \Lebran\Container\NotFoundException
     */
    public function testNotFoundClass()
    {
        $di = new Container();
        $di->get('test');
    }

    public function testRegisterServiceProvider()
    {
        $di = new Container();
        $di->register(new TestServiceProvider());
        $this->assertInstanceOf('Lebran\TestService', $di->get('test'));
    }

    public function testResolveParameters()
    {
        $di = new Container();
        $di->set('test', 'Lebran\TestService2');
        $service = $di->get('test', [
            'param3' => 'test',
            1 => 'test2'
        ]);
        $this->assertInstanceOf('Lebran\TestService', $service->param);
        $this->assertEquals('test2', $service->param1);
        $this->assertEquals('test3', $service->param2);
        $this->assertEquals('test', $service->param3);
    }

    /**
     * @expectedException \Lebran\ContainerException
     */
    public function testParameterNotPassed()
    {
        $di = new Container();
        $di->set('test', 'Lebran\TestService2')
           ->get('test');
    }

    public function testClosureThisMustBeContainer()
    {
        $di = new Container();
        $di->set('test', function(){
            return $this;
        });

        $this->assertSame($di, $di->get('test'));
    }

    /**
     * @param mixed $definition
     *
     * @dataProvider providerCall
     */
    public function testCall($definition)
    {
        $di = new Container();
        $this->assertInstanceOf('Lebran\TestServiceProvider', $di->call($definition));
    }

    public function providerCall()
    {
        return [
            [['Lebran\TestService','testCall']],
            [
                function(TestServiceProvider $test){
                    return $test;
                }
            ]
        ];
    }
}