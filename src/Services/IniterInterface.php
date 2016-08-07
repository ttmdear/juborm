<?php
namespace Juborm\Services;

use Juborm\Services\Manager;

interface IniterInterface
{
    public function booting(Manager $services);
    public function start(Manager $services);
    public function stop(Manager $services);
}
