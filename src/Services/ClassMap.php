<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Juborm\Services\Manager;
use Juborm\ClassMap\Manager as ORMClassMap;

/**
 * Service innter for class map.
 */
class ClassMap implements IniterInterface
{
    public function booting(Manager $services)
    {

    }

    public function start(Manager $services)
    {
        return new ORMClassMap();
    }

    public function stop(Manager $services)
    {

    }
}
