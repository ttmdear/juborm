<?php
namespace Juborm;

use Juborm\Services\Manager as Services;

abstract class ORM
{
    private static $services;

    /**
     * Return service with specific name. Or define new service.
     *
     * @param string @name Name of service to get, or to define.
     * @param Juborm\Services\IniterInterface|object @initer The class which
     * implements IniterInterface or already created object.
     */
    public static function service($name, $initer = null)
    {
        if (is_null(self::$services)) {
            self::$services = new Services();
        }

        if (!is_null($initer)) {
            return self::$services->define($name, $initer);
        }

        return self::$services->get($name);
    }
}
