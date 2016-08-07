<?php
namespace Juborm\ClassMap;

use Juborm\ORM;
use Juborm\ClassMap\ClassMap as ORMClassMap;

class Manager extends ORM
{
    private $managers = array();

    public function get($model)
    {
        if (isset($this->managers[$model])) {
            return $this->managers[$model];
        }

        $paths = $this->service('paths');
        $paths->setModel($model);

        $genMapOfClasses = $paths->genMapOfClasses();
        $mapOfClasses = $paths->mapOfClasses();

        $classMapGen = new ORMClassMap($genMapOfClasses);

        if (file_exists($mapOfClasses)) {
            $classMapGen->merge(new ORMClassMap($mapOfClasses));
        }

        $this->managers[$model] = $classMapGen;

        return $this->managers[$model];
    }
}
