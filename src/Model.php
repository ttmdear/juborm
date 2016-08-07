<?php
namespace Juborm;

use Juborm\ORM;

abstract class Model extends ORM
{
    protected $source;
    protected $model;

    public function adapter($source = null)
    {
        // services
        $assert = $this->service('assert');
        $sources = $this->service('sources');

        $source = is_null($source) ? $this->source() : $source;
        $adapter = $sources->get($source);
        $assert->notNull($adapter);

        return $adapter;
    }

    public function source($source = null)
    {
        if (is_null($source)) {
            if (!is_null($this->source)) {
                // zrodlo zostalo podane wiec zwracamy
                return $this->source;
            }

            // jesli zrodlo nie jest podane to defaultowe zrodlo to model
            return $this->model();
        }

        $this->source = $source;
        return $this;
    }

    public function model($model = null)
    {
        $assert = $this->service('assert');

        if (is_null($model)) {
            $assert->notNull($this->model, 'You should set model for Entity class.');

            return $this->model;
        }

        $this->model = $model;

        return $this;
    }
}
