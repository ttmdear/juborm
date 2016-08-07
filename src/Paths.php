<?php
namespace Juborm;

use Juborm\ORM;

class Paths extends ORM
{
    private $config;
    private $model;
    private $realpath = true;

    function __construct()
    {
        $this->config = $this->service('config');
    }

    public function setRealpath($realpath)
    {
        $this->realpath = $realpath;
        return $this;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function genMapOfClasses()
    {
        $dir = $this->dir();
        return "$dir/.genMapOfClasses.php";
    }

    public function mapOfClasses()
    {
        $dir = $this->dir();
        return "$dir/mapOfClasses.php";
    }

    public function genRelations()
    {
        $dir = $this->dir();
        return "$dir/.genRelations.php";
    }

    public function relations()
    {
        $dir = $this->dir();
        return "$dir/relations.php";
    }

    public function relationsTree()
    {
        $dir = $this->dir();
        return "$dir/.relationsTree";
    }

    public function relationsTreeIndex()
    {
        $relationsTree = $this->relationsTree();
        return "$relationsTree/index.php";
    }

    public function baseEntity($entity)
    {
        $baseEntities = $this->baseEntities();
        return "$baseEntities/$entity.php";
    }

    public function entity($entity)
    {
        $entities = $this->entities();
        return "$entities/$entity.php";
    }

    public function select($entity)
    {
        $selects = $this->selects();
        return "$selects/$entity.php";
    }

    public function baseEntities()
    {
        $entities = $this->entities();
        return "$entities/Base";
    }

    public function entities()
    {
        $dir = $this->dir();
        return "$dir/Entities";
    }

    public function selects()
    {
        $dir = $this->dir();
        return "$dir/Selects";
    }

    public function modelEntity()
    {
        $dir = $this->dir();
        return "$dir/Entity.php";
    }

    public function modelSelect()
    {
        $dir = $this->dir();
        return "$dir/Select.php";
    }

    public function dir()
    {
        $assert = $this->service('assert');

        $dir = $this->config->get('models.#1.dir', '/.', $this->model);

        if ($this->realpath) {
            $realpath = realpath($dir);
            if ($realpath === false) {
                $assert->exception("Can't create realpath for $dir.");
            }

            return $realpath;
        }else{
            return $dir;
        }
    }
}
