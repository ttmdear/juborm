<?php
namespace Juborm\Assistant;

use Juborm\ORM;

abstract class Operation extends ORM
{
    private $model;

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function model()
    {
        return $this->model;
    }

    protected function mkdir($directory)
    {
        if(!is_dir($directory)){
            mkdir($directory, '0777', true);
        }
    }
}
