<?php
namespace Juborm;

use Juborm\ORM;
use Juborm\Util\UtilInterface;

class Util extends ORM implements UtilInterface
{
    public function arrayHas($array, $index)
    {
        $assert = $this->service('assert');
        $assert->isArray($array);

        return in_array($index, array_keys($array));
    }
}
