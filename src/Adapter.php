<?php
namespace Juborm;

use Juborm\ORM;
use Juborm\Collection as ORMCollection;
use Juborm\Statement\Select as ORMSelect;
use Juborm\Statement\Delete as ORMDelete;
use Juborm\Statement\Update as ORMUpdate;
use Juborm\Statement\Insert as ORMInsert;

use Midata\Adapter as MidataAdapter;
use Midata\DML as MidataDML;

/**
 * This is adapter for Midata Adapter class.
 */
class Adapter extends ORM
{
    private $adapter;

    function __construct(MidataAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function all(ORMSelect $select, $indexes = array())
    {
        $result = $this->fetchAll($select);
        $entityClass = $select->entity();
        $cleanEntity = new $entityClass();

        $collection = new ORMCollection();
        $collection->source($select->source());

        $collection
            ->entity($entityClass)
            ->data($result)
        ;

        if (!isset($indexes['PRIMARY'])) {
            $pkDefinition = $cleanEntity->pkDefinition();
            $indexes['PRIMARY'] = $pkDefinition;
        }

        $collection->indexes($indexes);

        return $collection;
    }

    public function name()
    {
        return $this->adapter->name();
    }

    public function first(ORMSelect $select)
    {
        $selectClone = clone($select);
        $selectClone->limit(1);

        $collection = $this->all($selectClone);

        return $collection->first();
    }

    public function fetchAll(ORMSelect $select)
    {
        return $this->adapter->fetchAll($select->native());
    }

    public function fetchRow(ORMSelect $select)
    {
        return $this->adapter->fetchRow($select->native());
    }

    public function fetchCol(ORMSelect $select)
    {
        return $this->adapter->fetchCol($select->native());
    }

    public function fetchOne(ORMSelect $select)
    {
        return $this->adapter->fetchOne($select->native());
    }

    public function count(ORMSelect $select)
    {
        return $this->adapter->count($select->native());
    }

    /**
     * Return list of tables.
     *
     * @return array
     */
    public function tables()
    {
        return $this->adapter->tables();
    }

    public function table($table)
    {
        return $this->adapter->table($table);
    }

    /**
     * Execute any command.
     *
     * @param string $queryOrStatement
     * @return mixed The return result is depended from adapter
     */
    public function execute($queryOrStatement)
    {
        $assert = $this->service('assert');

        if (is_string($queryOrStatement)) {
            return $this->adapter->execute($queryOrStatement);
        }

        $class = get_class($queryOrStatement);
        switch ($class) {
        case ORMSelect::class:
        case ORMDelete::class:
        case ORMUpdate::class:
        case ORMInsert::class:
            return $this->adapter->execute($queryOrStatement->native());
            break;

        default:
            $assert->exception("Not supported type of statement $class.");
        }
    }

    public function dml($statement)
    {
        $assert = $this->service('assert');

        $dml = $this->adapter->dml($statement);

        switch ($statement) {
        case MidataDML::SELECT:
            $statement = new ORMSelect($dml);
            break;
        case MidataDML::DELETE:
            $statement = new ORMDelete($dml);
            break;
        case MidataDML::UPDATE:
            $statement = new ORMUpdate($dml);
            break;
        case MidataDML::INSERT:
            $statement = new ORMInsert($dml);
            break;

        default:
            $assert->exception("Not supported type of statement $statement.");
            break;
        }

        $statement->source($this->name());

        return $statement;
    }
}
