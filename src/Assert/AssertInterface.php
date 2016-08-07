<?php
namespace Juborm\Assert;

interface AssertInterface
{
    /**
     * Check if given file is readable. If not the exception is throw.
     *
     * @param string $path
     * @param string $msg
     *
     * @return string
     */
    public function isReadable($path, $msg = null);

    /**
     * Check if given variable is in given array.
     *
     * @param string $var
     * @param string $msg
     *
     * @return string
     */
    public function inArray($var, $array, $msg = null);

    /**
     * Check if given variable is array. If not the exception is throw.
     *
     * @param mixed $var
     * @param string $msg
     *
     * @return array
     */
    public function isArray($var, $msg = null);

    /**
     * Check if given class exists.
     *
     * @param string $class
     * @param string $msg
     *
     * @return string
     */
    public function classExists($class, $msg = null);

    public function isBoolean($var, $msg = null);
    public function assert($expr, $msg);
    public function notNull($var, $msg = null);
    public function hasIndex($array, $index, $msg = null);
    public function notHasIndex($array, $index, $msg = null);
    public function isString($var, $msg = null);
    public function isCallable($var, $msg = null);
    public function exception($msg = null);
}
