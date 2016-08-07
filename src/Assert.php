<?php
namespace Juborm;

use Juborm\ORM;
use Juborm\Assert\InvalidAssertion;
use Juborm\Assert\AssertInterface;
use Exception;

class Assert extends ORM implements AssertInterface
{
    public function isReadable($path, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "$path is not readable";

        if (!is_readable($path)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function inArray($var, $array, $msg = null)
    {
        $this->isArray($array);
        $msg = !is_null($msg) ? $msg : "The value should be in array.";

        if (!in_array($var, $array)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function isVector($var, $msg = null)
    {
        $this->isArray($var);

        $msg = !is_null($msg) ? $msg : "The value should be vector (array indexed with numbers).";

        $keys = array_keys($var);
        foreach ($keys as $key) {
            if (!is_int($key)) {
                throw new InvalidAssertion($msg);
            }
        }
    }

    public function isArray($var, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "The value should be array.";

        if (!is_array($var)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function classExists($class, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "The class $class do not exists.";
        if (!class_exists($class)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function isBoolean($var, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "The value should be boolean.";
        if (!is_bool($var)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function assert($expr, $msg)
    {
        $msg = !is_null($msg) ? $msg : "The assert is break.";

        if (((bool)$expr) === false) {
            throw new InvalidAssertion($msg);
        }
    }

    public function notNull($var, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "The value should not be null.";
        if (is_null($var)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function hasIndex($array, $index, $msg = null)
    {
        $this->isArray($array);
        $msg = !is_null($msg) ? $msg : "There are no index $index.";
        $keys = array_keys($array);

        if (!in_array($index, $keys)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function notHasIndex($array, $index, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "There are index $index.";
        $keys = array_keys($array);

        if (in_array($index, $keys)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function isString($var, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "The var should be string.";
        if (!is_string($var)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function isCallable($var, $msg = null)
    {
        $msg = !is_null($msg) ? $msg : "The var should be callable.";
        if (!is_callable($var)) {
            throw new InvalidAssertion($msg);
        }
    }

    public function exception($msg = null)
    {
        $msg = !is_null($msg) ? $msg : "Unknow error.";
        throw new Exception($msg);
    }
}
