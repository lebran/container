<?php
namespace Lebran;

use Closure;
use ArrayAccess;
use ReflectionClass;
use Lebran\Container\NotFoundException;
use Lebran\Container\InjectableInterface;
use Interop\Container\ContainerInterface;
use Lebran\Container\ServiceProviderInterface;

/**
 * Lebran\Di it's a component that implements Dependency Injection/Service Location patterns.
 * Supports string, object and anonymous function definition. Allows using the array syntax.
 *
 *                              Examples
 *  <code>
 *      $di = \Lebran\Di\Container();
 *
 *      // Using string definition
 *      $di->set('test', '\Lebran\App\TestController');
 *
 *      // Using object definition (singleton)
 *      $di->set('test',  new \Lebran\App\TestController('param1'));
 *
 *      // Using anonymous function definition
 *      $di->set('test',  function ($param1, $param2) {
 *          return new \Lebran\App\TestController($param1, $param2)
 *      });
 *
 *  </code>
 *
 * @package    Di
 * @version    2.0.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    GNU Licence
 * @copyright  2014 - 2015 Roman Kritskiy
 */
class Container implements ContainerInterface, ArrayAccess
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * Store services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * @var array
     */
    protected $shared = [];

    /**
     * Returns last container instance.
     *
     * @return Container
     */
    public static function instance()
    {
        return static::$instance;
    }

    /**
     * Initialisation
     */
    public function __construct()
    {
        static::$instance = $this;
    }

    /**
     * Registers a service in the services container.
     *
     * @param string $id         Service id.
     * @param mixed  $definition Service definition.
     * @param bool   $shared     Shared or not.
     *
     * @return self
     */
    public function set($id, $definition, $shared = false)
    {
        $id = trim(trim($id, '\\'));
        if (is_string($definition)) {
            $definition = trim(trim($definition, '\\'));
        }
        $this->services[$id] = compact('definition', 'shared');
        return $this;
    }

    /**
     * Registers a shared service in the services container.
     *
     * @param string $id         Service id.
     * @param mixed  $definition Service definition.
     *
     * @return self
     */
    public function shared($id, $definition)
    {
        return $this->set($id, $definition, true);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id     Identifier of the entry to look for.
     * @param array  $params Parameter for service construct.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id, array $params = [])
    {
        if (array_key_exists($id, $this->shared)) {
            return $this->shared[$id];
        }

        $shared = false;
        if (array_key_exists($id, $this->services)) {
            $definition = $this->services[$id]['definition'];
            if ($this->services[$id]['shared']) {
                $shared = true;
            }
        } else {
            $definition = $id;
        }
        $instance = $this->resolveService($definition, $params);

        if ($shared) {
            $this->shared[$id] = $instance;
        }

        if ($instance instanceof InjectableInterface) {
            $instance->setDi($this);
        }

        return $instance;
    }

    /**
     * Resolves the service.
     *
     * @param mixed $definition The definition of service.
     * @param array $params     Parameters for service construct.
     *
     * @return mixed Entry.
     * @throws ContainerException Error while retrieving the entry.
     * @throws NotFoundException No entry was found for this identifier.
     */
    protected function resolveService($definition, array $params)
    {
        switch (gettype($definition)) {
            case 'string':
                if (array_key_exists($definition, $this->services)) {
                    return $this->get($definition, $params);
                } else if (class_exists($definition)) {
                    $parameters = [];
                    $reflection = new ReflectionClass($definition);
                    if (($construct = $reflection->getConstructor())) {
                        $parameters = $this->resolveOptions(
                            $construct->getParameters(),
                            $params
                        );
                    }
                    return $reflection->newInstanceArgs($parameters);
                } else {
                    throw new NotFoundException('');
                }
                break;
            case 'object':
                if ($definition instanceof Closure) {
                    return call_user_func_array($definition->bindTo($this), $params);
                } else {
                    return clone $definition;
                }
                break;
            default:
                throw new ContainerException('');
        }
    }

    /**
     * Resolve parameters of service construct.
     *
     * @param array $dependencies
     * @param array $parameters
     *
     * @return array Resolved parameters.
     * @throws ContainerException Error while retrieving the entry.
     * @throws NotFoundException No entry was found for this identifier.
     */
    protected function resolveOptions(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);
                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        $resolved = [];
        foreach ($dependencies as $parameter) {
            /** @var \ReflectionParameter $parameter */
            if (array_key_exists($parameter->name, $parameters)) {
                $resolved[] = $parameters[$parameter->name];
            } else if (($type = $parameter->getClass())) {
                try{
                    $params = [];
                    if (array_key_exists($type->name, $parameters)) {
                        $params = $parameters[$type->name];
                        unset($parameters[$type->name]);
                    }
                    $resolved[] = $this->get($type->name, $params);
                } catch(ContainerException $e){
                    if ($parameter->isOptional()) {
                        $resolved[] = $parameter->getDefaultValue();
                    } else {
                        throw $e;
                    }
                }
            } else {
                if ($parameter->isOptional()) {
                    $resolved[] = $parameter->getDefaultValue();
                } else {
                    throw new ContainerException('');
                }
            }
        }

        return $resolved;
    }

    /**
     * Removes a service in the services container.
     *
     * @param string $id Service id.
     *
     * @return void
     */
    public function remove($id)
    {
        unset($this->services[$id]);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return array_key_exists($id, $this->services);
    }

    /**
     * Merge two containers into one.
     *
     * @param ServiceProviderInterface $provider Another container.
     *
     * @return self
     */
    public function register(ServiceProviderInterface $provider)
    {
        $provider->register($this);
        return $this;
    }

    /**
     * Check whether the service is shared or not.
     *
     * @param string $id Service id.
     *
     * @return bool True if shared, false - not.
     */
    public function isShared($id)
    {
        return $this->has($id)?$this->services[$id]['shared']:false;
    }

    /**
     * Sets if the service is shared or not.
     *
     * @param string $id     Service id.
     * @param bool   $shared Shared or not.
     *
     * @return self
     */
    public function setShared($id, $shared = true)
    {
        if ($this->has($id)) {
            $this->services[$id]['shared'] = $shared;
        }
        return $this;
    }

    /**
     * Allows to register a shared service using the array syntax.
     *
     * @param string $id         Service id.
     * @param mixed  $definition Service definition.
     *
     * @return self
     */
    public function offsetSet($id, $definition)
    {
        return $this->set($id, $definition);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }

    /**
     * Removes a service from the services container using the array syntax.
     *
     * @param string $id Service id.
     *
     * @return void
     */
    public function offsetUnset($id)
    {
        $this->remove($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function offsetExists($id)
    {
        return $this->has($id);
    }

    /**
     * Allows to register a shared service using the array syntax.
     *
     * @param string $id         Service id.
     * @param mixed  $definition Service definition.
     *
     * @return self
     */
    public function __set($id, $definition)
    {
        $this->set($id, $definition, true);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function __get($id)
    {
        return $this->get($id);
    }

    /**
     * Removes a service from the services container using the array syntax.
     *
     * @param string $id Service id.
     *
     * @return void
     */
    public function __unset($id)
    {
        $this->remove($id);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function __isset($id)
    {
        return $this->has($id);
    }
}