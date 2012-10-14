<?php
namespace Demiurge;

class Demiurge
{
    private $services = array();

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
        if (!$this->isDynamic($value)) {
            $value = function() use ($value) { return $value; };
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
        return $this->__call($name);
    }

    public function __call($name, $arguments = array())
    {
        if (!isset($this->services[$name]))
            throw new \OutOfBoundsException(sprintf('Service $s is not defined', $name));

        array_unshift($arguments, $this);

        return call_user_func_array($this->services[$name], $arguments);
    }

    /**
     * Tells if the $value is what Demiurge consider a callable.
     *
     * @param mixed $value
     * @return bool
     */
    private function isDynamic($value)
    {
        if (is_object($value) && method_exists($value, '__invoke'))
            return true;

        return false;
    }
}
