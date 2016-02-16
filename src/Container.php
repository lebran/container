<?php
namespace Lebran;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use Lebran\Container\NotFoundException;
use Lebran\Container\InjectableInterface;
use Interop\Container\ContainerInterface;
use Lebran\Container\ServiceProviderInterface;

/**
 * Lebran\Container it's a component that implements Dependency Injection/Service Location patterns.
 * Supports string, object and anonymous function definition. Allows using the array and magical syntax.
 *
 *                              Example
 *  <code>
 *      // Create service container
 *      $di = new \Lebran\Container();
 *
 *      // Container supports 3 types of definition
 *
 *      // Type 1: Object
 *      $di->set('myservice', new \MyNamespace\MyService());
 *
 *      // Type 2: String
 *      $di->set('myservice2', '\MyNamespace\MyService2');
 *
 *      // Type 3: Closure
 *      $di->set('myservice3', function(){
 *          return new \MyNamespace\MyService3();
 *      });
 *
 *      // Getting service
 *      $di->get('myservice');
 *  </code>
 *
 * @package    Container
 * @version    1.0
 * @author     Roman Kritskiy <itoktor@gmail.com>
 * @license    MIT
 * @copyright  2015 - 2016 Roman Kritskiy
 */
class Container implements ContainerInterface, ArrayAccess
{
    const MAX_DEPENDENCY_LEVEL = 30;

    /**
     * @var self Store for last container instance
     */
    protected static $instance;

    /**
     * @var array Store for services.
     */
    protected $services = [];

    /**
     * @var array Store for shared services.
     */
    protected $shared = [];

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * Returns last container instance.
     *
     * @return Container Last container instance.
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
     * Merge two containers into one.
     *
     * @param ServiceProviderInterface $provider Service provider.
     *
     * @return self
     */
    public function register(ServiceProviderInterface $provider)
    {
        $provider->register($this);
        return $this;
    }

    /**
     * Registers a service in the container.
     *
     * @param string $id         Service id.
     * @param mixed  $definition Service definition.
     * @param bool   $shared     Shared or not.
     *
     * @return $this
     * @throws ContainerException Error while retrieving the entry.
     */
    public function set($id, $definition, $shared = false)
    {
        if (is_string($definition)) {
            $definition = $this->normalize($definition);
        }
        $this->services[$this->normalize($id)] = compact('definition', 'shared');
        return $this;
    }

    /**
     * Normalize service name.
     *
     * @param string $id Service id.
     *
     * @return string Normalized name.
     */
    protected function normalize($id)
    {
        return trim(trim($id), '\\');
    }

    /**
     * Registers a shared service in the container.
     *
     * @param string $id         Service id.
     * @param mixed  $definition Service definition.
     *
     * @return $this
     */
    public function shared($id, $definition)
    {
        return $this->set($id, $definition, true);
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
        return $this->has($id) ? $this->services[$id]['shared'] : false;
    }

    /**
     * Sets if the service is shared or not.
     *
     * @param string $id     Service id.
     * @param bool   $shared Shared or not.
     *
     * @return self
     * @throws NotFoundException No entry was found for this identifier.
     */
    public function setShared($id, $shared = true)
    {
        if ($this->has($id)) {
            $this->services[$id]['shared'] = $shared;
        } else {
            throw new NotFoundException('');
        }
        return $this;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id         Identifier of the entry to look for.
     * @param array  $parameters Parameter for service construct.
     *
     * @throws NotFoundException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id, array $parameters = [])
    {
        if($this->level++ > static::MAX_DEPENDENCY_LEVEL){
            throw new ContainerException('Circular dependency.');
        }

        if (array_key_exists($id, $this->shared)) {
            return $this->shared[$id];
        }

        $instance = $this->resolveService(
            $this->has($id) ? $this->services[$id]['definition'] : $id,
            $parameters
        );

        if ($this->has($id) && $this->services[$id]['shared']) {
            $this->shared[$id] = $instance;
        }

        if ($instance instanceof InjectableInterface) {
            $instance->setDi($this);
        }
        $this->level = 0;
        return $instance;
    }

    /**
     * Resolves the service.
     *
     * @param mixed $definition The definition of service.
     * @param array $parameters Parameters for service construct.
     *
     * @return mixed Entry.
     * @throws ContainerException Error while retrieving the entry.
     * @throws NotFoundException No entry was found for this identifier.
     */
    protected function resolveService($definition, array $parameters = [])
    {
        switch (gettype($definition)) {
            case 'string':
                if ($this->has($definition)) {
                    return $this->get($definition, $parameters);
                } else if (class_exists($definition)) {
                    $reflection = new ReflectionClass($definition);
                    if (($construct = $reflection->getConstructor())) {
                        $parameters = $this->resolveOptions(
                            $construct->getParameters(),
                            $parameters
                        );
                    }
                    return $reflection->newInstanceArgs($parameters);
                } else {
                    throw new NotFoundException('');
                }
            case 'object':
                if ($definition instanceof Closure) {
                    return call_user_func_array($definition->bindTo($this), $parameters);
                } else {
                    return clone $definition;
                }
            default:
                throw new ContainerException('Type of definition is not correct.');
        }
    }

    /**
     * Resolve callback dependencies and executes him.
     *
     * @param callable $callback
     * @param array    $parameters
     *
     * @return mixed
     * @throws ContainerException Error while retrieving the entry.
     * @throws NotFoundException No entry was found for this identifier.
     */
    public function call(callable $callback, array $parameters = [])
    {
        if (is_array($callback)) {
            $reflection = new ReflectionMethod($callback[0], $callback[1]);
        } else {
            $reflection = new ReflectionFunction($callback);
        }

        return call_user_func_array(
            $callback,
            $this->resolveOptions(
                $reflection->getParameters(),
                $parameters
            )
        );
    }

    /**
     * Resolve parameters of service.
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
        $resolved = [];
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);
                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        foreach ($dependencies as $parameter) {
            /** @var ReflectionParameter $parameter */
            if (array_key_exists($parameter->name, $parameters)) {
                $resolved[] = $parameters[$parameter->name];
            } else if (($type = $parameter->getClass())) {
                $type       = $type->name;
                $resolved[] = $this->get(
                    $type,
                    array_key_exists($type, $parameters) ? $parameters[$type] : []
                );
            } else if ($parameter->isDefaultValueAvailable()) {
                $resolved[] = $parameter->getDefaultValue();
            } else {
                throw new ContainerException('Parameter "'.$parameter->name.'" not passed.');
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
        $this->shared($id, $definition);
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