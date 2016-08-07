<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Juborm\Services\Manager;

use Juborm\Relations\Manager as RelationsManager;

class Relations implements IniterInterface
{
    public function booting(Manager $services)
    {

    }

    public function start(Manager $services)
    {
        return new RelationsManager();
    }

    public function stop(Manager $services)
    {

    }
}
