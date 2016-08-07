<?php
namespace Juborm\Statement;

use Juborm\Statement as ORMStatement;
use Midata\DML\Insert as MidataInsert;

class Insert extends ORMStatement
{
    private $native;

    function __construct(MidataInsert $native)
    {
        $this->native = $native;
    }

    public function table($table = null)
    {
        $this->native->table($table);
        return $this;
    }

    public function values($values = null)
    {
        $this->native->values($values);
        return $this;
    }

    public function native()
    {
        return $this->native;
    }
}
