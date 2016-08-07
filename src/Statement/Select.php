<?php
namespace Juborm\Statement;

use Juborm\Statement as ORMStatement;

use Midata\DML as MidataDML;
use Midata\DML\Conditional\Select as MidataSelect;

use Countable;
use Iterator;

class Select extends ORMStatement implements Countable, Iterator
{
    private $entity;
    private $select;

    // Iterator
    private $collection;
    private $position = 0;

    public function prepare(){}

    function __construct(MidataSelect $select)
    {
        $this->select = $select;
    }

    // Iterator
    public function rewind()
    {
        $this->collection = $this->all();
        $this->position = 0;
    }

    public function current()
    {
        return $this->collection->get($this->position);
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        if ($this->collection->count() == 0) {
            // kolekcja jest pusta
            return false;
        }

        if (is_null($this->collection->get($this->position))) {
            return false;
        }

        return true;
    }

    /**
     * The method return present entity of select, or you can set entity by
     * first parameter.
     *
     * @param string @entity Use if you want to set entity class for select.
     * Entity class is use by method first() and all().
     * @return string|Juborm\Entity
     */
    public function entity($entity = null)
    {
        $assert = $this->service('assert');

        if (is_null($entity)) {
            $assert->notNull($this->entity);
            return $this->entity;
        }

        $assert->classExists($entity);
        $this->entity = $entity;

        return $this;
    }

    /**
     * Removes all rows from the base table that meet the condition .
     *
     * @return Juborm\Collection Deleted collection of rows.
     */
    public function delete()
    {
        return $this->all()->delete();
    }

    public function from($table = null)
    {
        if (is_null($table)) {
            return $this->select->from();
        }

        $this->select->from($table);

        return $this;
    }

    /**
     * Return alias of from, in select statement.
     * Alias is set by from method.
     *
     * @return string
     */
    public function alias()
    {
        return $this->select->alias();
    }

    /**
     * Return the table from "FROM" of select.
     *
     * @return string
     */
    public function table()
    {
        return $this->select->table();
    }

    // where
    public function brackets($function)
    {
        $this->select->brackets($function, $this);
        return $this;
    }

    public function andOperator()
    {
        $this->select->andOperator();
        return $this;
    }

    public function orOperator()
    {
        $this->select->orOperator();
        return $this;
    }

    public function equal($column, $to)
    {
        if ($to instanceof self) {
            $to = $to->native();
        }

        $this->select->equal($column, $to);
        return $this;
    }

    public function in($column, $in)
    {
        if ($in instanceof self) {
            $in = $in->native();
        }

        $this->select->in($column, $in);
        return $this;
    }

    public function like($column, $like)
    {
        $this->select->like($column, $like);
        return $this;
    }

    public function isNull($column)
    {
        $this->select->isNull($column);
        return $this;
    }

    public function isNotNull($column)
    {
        $this->select->isNotNull($column);
        return $this;
    }

    public function startWith($column, $like)
    {
        $this->select->startWith($column, $like);
        return $this;
    }

    public function endWith($column, $like)
    {
        $this->select->endWith($column, $like);
        return $this;
    }

    public function contains($column, $like)
    {
        $this->select->contains($column, $like);
        return $this;
    }

    public function expr($expr)
    {
        $this->select->expr($expr);
        return $this;
    }

    public function where()
    {
        $this->select->where();
        return $this;
    }

    // joins
    public function innerJoin($table, $condition, $columns = array())
    {
        $this->select->innerJoin($table, $condition, $columns);
        return $this;
    }

    public function leftJoin($table, $condition, $columns = array())
    {
        $this->select->leftJoin($table, $condition, $columns);
        return $this;
    }

    public function rightJoin($table, $condition, $columns = array())
    {
        $this->select->rightJoin($table, $condition, $columns);
        return $this;
    }

    public function outerJoin($table, $condition, $columns = array())
    {
        $this->select->outerJoin($table, $condition, $columns);
        return $this;
    }

    /**
     * Use this method if you want to change pointer UP to last joined table.
     * This has influence to join condition at join methods.
     */
    public function pointer($alias = null)
    {
        $this->select->pointer($alias);
        return $this;
    }

    /**
     * Create select statement base on relation.
     *
     * @param string @relation It is relation path like books:author or
     * books@books_ware:warehouse.
     * @param array @aliases If you wan't to use relations joins more than
     * once, you should set aliases for each table if relation path. To avoid
     * unexpected aliases.
     * @param array @columns
     */
    public function relationJoin($relation, $aliases = array(), $columns = array())
    {
        $relations = $this->service('relations')->get($this->model());

        $relation;
        if ($relation[0] == "$") {
            $relation = $this->table().$relation;
        }else{
            $relation = $this->table().':'.$relation;
        }

        $joins = $relations->get($relation);

        foreach ($joins as $table => $condition) {
            if(isset($aliases[$table])){
                $alias = $aliases[$table];
                $condition = str_replace("$table.", "$alias.", $condition);
                $table = array($aliases[$table] => $table);
            }

            $this->innerJoin($table, $condition);
        }

        foreach ($columns as $alias => $value) {
            if (is_int($alias)) {
                $this->column($value);
            }else{
                $this->column($value, $alias);
            }
        }

        return $this;
    }

    /**
     * Add column do select statement.
     *
     * @param string|array @value It can be
     * @param string @alias
     * @return Juborm\Statement\Conditional\Select;
     *
     * @example "
     * $select->column('author_id');
     * // ...
     * // authors.author_id as author_id
     *
     * $select->column('author_id', 'idOfAuthor);
     * // ...
     * // authors.author_id as idOfAuthor
     *
     * $select->column('books.author_id', 'idOfAuthor);
     * // ...
     * // books.author_id as idOfAuthor
     *
     * $select->column(array('author_id', 'first_name));
     * // ...
     * // authors.author_id as author_id
     * // authors.first_name as first_name
     *
     * $select->column(array(
     *  'author_id' => 'idOfAuthor',
     *  'first_name => 'firstName',
     * ));
     * // ...
     * // authors.author_id as idOfAuthor
     * // authors.first_name as firstName
     * "
     */
    public function column($value, $alias = null)
    {
        $this->select->column($value, $alias);
        return $this;
    }

    /**
     * Return columns of select.
     *
     * @return array All select column (alias => value)
     */
    public function columns()
    {
        return $this->select->columns();
    }

    public function limit($limit = null , $offset = 0)
    {
        $this->select->limit($limit, $offset);
        return $this;
    }

    /**
     * Add order to select.
     *
     * @param string @column
     * @param string @type ASC|DESC
     * @return self
     */
    public function order($column = null, $type = MidataSelect::ORDER_ASC)
    {
        $this->select->order($column, $type);
        return $this;
    }

    /**
     * Return select orders
     *
     * @return array Array with all order of Select
     */
    public function orders()
    {
        return $this->select->orders();
    }

    public function native()
    {
        return $this->select;
    }

    // fetch data methods
    public function all($source = null, $indexes = array())
    {
        $adapter = $this->adapter($source);
        return $adapter->all($this, $indexes);
    }

    public function first($source = null)
    {
        $adapter = $this->adapter($source);
        return $adapter->first($this);
    }

    public function count($source = null)
    {
        $adapter = $this->adapter($source);
        return $adapter->count($this);
    }

    public function fetchAll($source = null)
    {
        $adapter = $this->adapter($source);
        return $adapter->fetchAll($this);
    }

    public function fetchRow($source = null)
    {
        $adapter = $this->adapter($source);
        return $adapter->fetchRow($this);
    }

    public function fetchCol($source = null)
    {
        $adapter = $this->adapter($source);
        return $adapter->fetchCol($this);
    }

    public function fetchOne($source = null)
    {
        $adapter = $this->adapter($source);
        return $adapter->fetchOne($this);
    }
}
