<?php
namespace Juborm\Statement;

use Juborm\Statement as ORMStatement;
use Midata\DML\Conditional\Delete as MidaDelete;

class Delete extends ORMStatement
{
    private $native;

    function __construct(MidaDelete $native)
    {
        $this->native = $native;
    }

    public function native()
    {
        return $this->native;
    }

    public function table($table = null)
    {
        $this->native->table($table);
        return $this;
    }

    // where
    public function brackets($function)
    {
        $this->native->brackets($function, $this);
        return $this;
    }

    public function andOperator()
    {
        $this->native->andOperator();
        return $this;
    }

    public function orOperator()
    {
        $this->native->orOperator();
        return $this;
    }

    public function equal($column, $to)
    {
        $this->native->equal($column, $to);
        return $this;
    }

    public function in($column, $in)
    {
        $this->native->in($column, $in);
        return $this;
    }

    public function like($column, $like)
    {
        $this->native->like($column, $like);
        return $this;
    }

    public function isNull($column)
    {
        $this->native->isNull($column);
        return $this;
    }

    public function isNotNull($column)
    {
        $this->native->isNotNull($column);
        return $this;
    }

    public function startWith($column, $like)
    {
        $this->native->startWith($column, $like);
        return $this;
    }

    public function endWith($column, $like)
    {
        $this->native->endWith($column, $like);
        return $this;
    }

    public function contains($column, $like)
    {
        $this->native->contains($column, $like);
        return $this;
    }

    public function expr($expr)
    {
        $this->native->expr($expr);
        return $this;
    }

    public function where()
    {
        $this->native->where();
        return $this;
    }

}
