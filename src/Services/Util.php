<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Juborm\Services\Manager;
use Juborm\Util as ORMUtil;

class Util implements IniterInterface
{
    public function booting(Manager $services)
    {

    }

    public function start(Manager $services)
    {
        return new ORMUtil();
    }

    public function stop(Manager $services)
    {

    }
}
