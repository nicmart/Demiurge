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
     * @param string $name
     * @param mixed $value
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
        if (method_exists($this, $name)) {
            return $this->$name();
        } elseif (method_exists($this, $methodName = 'get' . ucfirst($name))) {
            return $this->$methodName();
        }

        return $this->__call($name);
    }

    /**
     * Get a value with method access
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function __call($name, $arguments = array())
    {
        if (!$this->hasService($name))
            throw new \OutOfBoundsException(sprintf('Service $s is not defined', $name));

        array_unshift($arguments, $this);

        return call_user_func_array($this->services[$name], $arguments);
    }

    /**
     * Check if the service exists.
     *
     * @param string $name
     * @return bool
     */
    public function hasService($name)
    {
        return method_exists($this, $name) || (method_exists($this, $methodName = 'get' . ucfirst($name)))
            || isset($this->services[$name]);
    }
}
