<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Juborm\Services\Manager;
use Juborm\Paths as ORMPaths;

class Paths implements IniterInterface
{
    public function booting(Manager $services)
    {

    }

    public function start(Manager $services)
    {
        return new ORMPaths();
    }

    public function stop(Manager $services)
    {

    }
}
