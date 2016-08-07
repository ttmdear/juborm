<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Juborm\Services\Manager;

use Juborm\Config\Config as ConfigManager;

class Config implements IniterInterface
{
    public function booting(Manager $services)
    {

    }

    public function start(Manager $services)
    {
        return new ConfigManager();
    }

    public function stop(Manager $services)
    {

    }
}
