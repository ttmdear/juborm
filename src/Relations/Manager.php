<?php
namespace Juborm\Relations;

use Juborm\ORM;
use Juborm\Relations\Relations as ORMRelations;

class Manager extends ORM
{
    private $relations = array();

    public function get($model)
    {
        if (!isset($this->relations[$model])) {
            $this->init($model);
        }

        return isset($this->relations[$model]) ? $this->relations[$model] : null;
    }

    private function init($model)
    {
        $this->relations[$model] = ORMRelations::factory($model);
    }
}
