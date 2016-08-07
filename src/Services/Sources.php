<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Juborm\Services\Manager as ServicesManager;
use Juborm\Sources as ORMSources;

class Sources implements IniterInterface
{
    public function booting(ServicesManager $services)
    {

    }

    public function start(ServicesManager $services)
    {
        return new ORMSources();
    }

    public function stop(ServicesManager $services)
    {

    }
}
