<?php
namespace Juborm\Relations;

use Juborm\ORM;

class Relations extends ORM
{
    private $relationsTree;
    private $relationsTreeIndex;
    private $index = array();

    public static function factory($model)
    {
        $paths = static::service('paths');
        $paths->setModel($model);

        $relations = new self();
        $relations
            ->setRelationsTree($paths->relationsTree())
            ->setRelationsTreeIndex($paths->relationsTreeIndex())
        ;

        $relations->load();

        return $relations;
    }

    public function load()
    {
        $this->index = include($this->relationsTreeIndex);
    }

    public function setRelationsTree($relationsTree)
    {
        $this->relationsTree = $relationsTree;
        return $this;
    }

    public function setRelationsTreeIndex($relationsTreeIndex)
    {
        $this->relationsTreeIndex = $relationsTreeIndex;
        return $this;
    }

    public function get($relation)
    {
        $fileName = $this->fileName($relation);

        $joins = unserialize(file_get_contents($this->relationsTree.'/'.$fileName));

        return $joins;
    }

    private function fileName($relation)
    {
        $assert = $this->service('assert');

        if (isset($this->index[$relation])) {
            $indexed = $this->index[$relation];
            if (count($indexed) > 1) {
                $assert->exception("Relation $relation is not clear.");
            }

            return $this->index[$relation][0];
        }else{
            $assert->exception("$relation is not found.");
        }
    }
}
