<?php
namespace Juborm;

use Juborm\Model as ORMModel;

abstract class Statement extends ORMModel
{
    abstract public function native();

    public function execute($source = null)
    {
        return $this->adapter($source)->execute($this);
    }

    public function sql()
    {
        return $this->native()->sql();
    }

    public function bind($bind, $value)
    {
        $this->native()->bind($bind, $value);
        return $this;
    }

    public function binds($binds = null)
    {
        $this->native()->binds($binds);
        return $this;
    }
}
