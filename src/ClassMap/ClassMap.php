<?php
namespace Juborm\ClassMap;

use Juborm\ORM;

class ClassMap extends ORM
{
    private $classes = array();

    function __construct($path)
    {
        $assert = $this->service('assert');
        $assert->isReadable($path);

        $classes = include($path);
        $assert->isArray($classes, 'ClassMap should be array.');
        $this->classes = $classes;
    }

    public function merge(ClassMap $classMap)
    {
        $this->classes = array_merge_recursive($this->classes, $classMap->toArray());
        return $this;
    }

    public function toArray()
    {
        return $this->classes;
    }

    public function get($name)
    {
        $assert = $this->service('assert');
        $assert->hasIndex($this->classes['entities'], $name, "There are no entity $name");

        return $this->classes['entities'][$name];
    }

    public function select($name)
    {
        $assert = $this->service('assert');
        $assert->hasIndex($this->classes['selects'], $name, "There are no selects $name");

        return $this->classes['selects'][$name];
    }
}
