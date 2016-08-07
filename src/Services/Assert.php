<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Juborm\Services\Manager;

use Juborm\Assert as ORMAssert;

class Assert implements IniterInterface
{
    public function booting(Manager $services)
    {

    }

    public function start(Manager $services)
    {
        return new ORMAssert();
    }

    public function stop(Manager $services)
    {

    }
}
