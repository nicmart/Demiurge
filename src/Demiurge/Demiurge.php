<?php
namespace Demiurge;

/**
 * The Demiurge class.
 */
class Demiurge
{
    private $services = array();

    /**
     * Returns a function that simply returns $value
     *
     * @param mixed $value
     * @return callable
     */
    public static function protect($value)
    {
        return function() use ($value) {
            return $value;
        };
    }

    /**
     * Transform a callable into another one that execute the first anly the first time,
     * and returns always the first return value.
     *
     * @param mixed $value
     * @return callable
     */
    public static function share($value)
    {
        if (static::is_dynamic($value)) {
            return function (Demiurge $d) use ($value) {
                static $object;

                if (null === $object) {
                    $object = call_user_func($value, $d);
                }

                return $object;
            };
        }

        return $value;
    }

    /**
     * Tells if the $value is what Demiurge consider a callable.
     *
     * @param mixed $value
     * @return bool
     */
    private static function is_dynamic($value)
    {
        if (is_object($value) && method_exists($value, '__invoke'))
            return true;

        return false;
    }

    /**
     * Set a value or a service.
     * If $value is a Closure or an object that implements the __invoke method,
     * when the value will be retrivied the callback will be executed.
     * Otherwise it will returned the plain value.
     *
     * @param string $name      The name of the service
     * @param mixed $value      The value or closure that will generate the service
     */
    public function __set($name, $value)
    {
        if (!$this->is_dynamic($value)) {
            $value = $this->protect($value);
        }

        $this->services[$name] = $value;
    }

    /**
     * Get a value.
     *
     * @param string $name
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function __get($name)
    {
        return call_user_func_array($this->getRawService($name), array($this));
    }

    /**
     * Get a value with method access
     *
     * @param string $name              The name of the service
     * @param array $arguments          The arguments to pass to the service definition
     * @return mixed                    The service
     * @throws \OutOfBoundsException
     */
    public function __call($name, $arguments = array())
    {
        array_unshift($arguments, $this);

        return call_user_func_array($this->getRawService($name), $arguments);
    }

    /**
     * Check if the service exists.
     *
     * @param string $name  The name of the service
     * @return bool         True if the service is defined in the container, false otherwise
     */
    public function hasService($name)
    {
        try {
            $this->getRawService($name);
        } catch (\OutOfBoundsException $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns the the callable that will returns the service
     *
     * @param string $name                     The name of the service
     * @return Callable                 The callable that will generate the service
     * @throws \OutOfBoundsException    Thrown if the service is not defined
     */
    public function getRawService($name)
    {
        if (isset($this->services[$name]))
            return $this->services[$name];

        if (method_exists($this, $name)) {
            return array($this, $name);
        }

        if (method_exists($this, $methodName = 'get' . ucfirst($name))) {
            return array($this, $methodName);
        }

        throw new \OutOfBoundsException(sprintf("Service '$name' is not defined", $name));
    }
}
