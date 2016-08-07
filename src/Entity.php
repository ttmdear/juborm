<?php
namespace Juborm;

use Juborm\Statement\Select as ORMSelect;
use Juborm\Statement\Delete as ORMDelete;
use Juborm\Model as ORMModel;

use Midata\DML as MidataDML;
use ArrayAccess;

abstract class Entity extends ORMModel implements ArrayAccess
{
    CONST STATE_NEW = 'new';
    CONST STATE_DELETED = 'deleted';
    CONST STATE_FETCHED = 'fetched';

    private $data = array();
    private $dataModified = array();
    private $state;

    protected static $table;
    protected static $fields;
    protected static $pk;
    protected static $select;

    /**
     * Create new instance of entity.
     *
     * @param array $data Init data
     * @return Entity;
     */
    public static function fetchNew($source = null)
    {
        $entity = new static();
        $entity->state(self::STATE_NEW);
        $entity->source($source);

        return $entity;
    }

    /**
     * Create and return instance of Select Statement for entity. If entity
     * has defined select, the return object will be this instance.
     *
     * @return Juborm\Select Statement of select
     */
    public static function select($columns = null)
    {
        if(is_string($columns)){
            // zostala podana tylko jedna kolumna wiec zamieniam na arraya
            $columns = array($columns);
        }

        // wczytuje klase select
        $select = is_null(static::$select) ? ORMSelect::class : static::$select;

        $cleanEntity = new static();
        $select = $cleanEntity->adapter()->dml(MidataDML::SELECT);

        $select
            ->from(static::$table)
            ->entity(static::class)
            // the select source is overwrite by Entity sourece.
            ->model($cleanEntity->model())
            ->source($cleanEntity->source())
        ;

        if(is_null($columns)){
            $columns = static::fields();
        }

        // columns of primary key are added to each entity
        $pks = static::pkDefinition();

        foreach($pks as $pk){
            if(!in_array($pk, $columns)){
                $columns[] = $pk;
            }
        }

        foreach($columns as $alias => $value) {
            if (is_int($alias)) {
                $select->column($value);
            }else{
                $select->column($value, $alias);
            }
        }

        // the last step is prepare the select to use
        $select->prepare();

        // endtodo
        return $select;
    }

    public function offsetSet($offset, $value)
    {
        $assert = $this->service('assert');
        if (is_null($offset)) {
            $assert->exception("You can't use array append on entity.");
        } else {
            $this->set($offset, $value);
        }
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    public function offsetGet($offset)
    {
        $has = $this->has($offset);
        if (!$has) {
            return null;
        }

        return $this->get($offset);
    }

    /**
     * Return list of entity fields.
     */
    public static function fields()
    {
        return array_keys(static::$fields);
    }

    /**
     * Return the table name of entity.
     *
     * @return string
     */
    public static function table()
    {
        $assert = static::service('assert');
        $assert->notNull(static::$table, 'Each entity should have defined table.');

        return static::$table;
    }

    /**
     * Return values of primary key column.
     *
     * @return string
     */
    public function pk()
    {
        $assert = $this->service('assert');

        $definition = $this->pkDefinition();

        if($this->hasComplexPk()){
            $assert->exception("The entity has complex key, therefor you should use method pkArray.");
        }

        return $this->get($definition[0]);
    }

    /**
     * Check if entity has complex key, if not return false.
     *
     * @return bool
     */
    public static function hasComplexPk()
    {
        $definition = self::pkDefinition();

        if(count($definition) != 1){
            return true;
        }

        return false;
    }

    /**
     * Return array with values of primary key columns.
     *
     * @return array
     */
    public function pkArray()
    {
        $definition = $this->pkDefinition();

        $pk = array();

        foreach($definition as $pkColumnName){
            $pk[$pkColumnName] = $this->data[$pkColumnName];
        }

        return $pk;
    }

    /**
     * Return array with definition of primary key.
     *
     * @return array
     */
    public static function pkDefinition()
    {
        $assert = static::service('assert');
        $assert->notNull(static::$pk, "Each entity must have a primary key.");
        $assert->isArray(static::$pk, "Definition of primary key should be array.");

        return static::$pk;
    }

    /**
     * Return present value of specific attribute.
     *
     * @param string $name Name of entity attribute.
     * @return string
     */
    public function get($name)
    {
        if(array_key_exists($name, $this->dataModified)){
            return $this->dataModified[$name];
        }

        if(array_key_exists($name, $this->data)){
            return $this->data[$name];
        }

        return null;
    }

    /**
     * Set the new value to entity attribute.
     *
     * @param string $name Name of entity attribute.
     * @param string $value Name of entity attribute.
     * @return string
     */
    public function set($name, $value)
    {
        $this->dataModified[$name] = $value;
        return $this;
    }

    /**
     * Method return already prepared select to specific relation between
     * entities.
     *
     * @param string $relation Paths which represents relation between
     * entities.
     * @return Juborm\Statement\Select Statement of select
     */
    public function relation($relation)
    {
        $table = $this->table();
        $relation = "$relation:$table";

        $len = strlen($relation);

        $firstColon = strpos($relation, ":");
        $firstColon = ($firstColon === false) ? $len : $firstColon;

        $firstDol = strpos($relation, "$");
        $firstDol = ($firstDol === false) ? $len : $firstDol;

        $firstSep = ($firstDol < $firstColon) ? $firstDol : $firstColon;

        $entity = substr($relation, 0, $firstSep);
        $relation = substr($relation, $firstSep);

        if ($relation[0]==":") {
            $relation = substr($relation, 1);
        }

        $classes = $this->service('classes');
        $mapOfClasses = $classes->get($this->model());

        $entity = $mapOfClasses->get($entity);
        $select = $entity::select();
        $select->relationJoin($relation);

        foreach ($this->pkDefinition() as $pk) {
            $select->equal("$table.$pk", $this->get($pk));
        }

        return $select;
    }

    /**
     * Set the state of entity or return if parameter is not set.
     * The state of entity determines the behaviour
     * during save method.
     *
     * @param string $state new|deleted|fetched
     * @return self|string
     */
    public function state($state = null)
    {
        if (is_null($state)) {
            return $this->state;
        }

        $assert = $this->service('assert');
        $assert->inArray($state, array(
            self::STATE_DELETED,
            self::STATE_FETCHED,
            self::STATE_NEW,
        ));

        $this->state = $state;

        return $this;
    }

    /**
     * This method clean all entity data, and set new. Or return present.
     *
     * @param array $data
     * @return self
     */
    public function data($data = null)
    {
        if (is_null($data)) {
            return array_merge($this->data, $this->dataModified);
        }

        $this->data = $data;
        $this->dataModified = array();

        return $this;
    }

    public function save()
    {
        $assert = $this->service('assert');

        switch ($this->state) {
        case self::STATE_DELETED:
        case self::STATE_NEW:

            $insert = $this->adapter()->dml(MidataDML::INSERT);
            $table = $this->table();

            $values = array();
            foreach ($this->fields() as $field) {
                if (!$this->has($field)) {
                    continue;
                }

                $values[$field] = $this->get($field);
            }

            $insert
                ->table($table)
                ->values($values)
                ->source($this->source())
            ;

            if(!$this->hasComplexPk()){
                $pk = $insert->execute();
                $definition = $this->pkDefinition();
                $this->set($definition[0], $pk);
                return $pk;
            }

            break;
        case self::STATE_FETCHED:
            $table = $this->table();

            $values = array();
            foreach ($this->fields() as $field) {
                if (!$this->has($field)) {
                    continue;
                }

                $values[$field] = $this->get($field);
            }

            $update = $this->adapter()->dml(MidataDML::UPDATE);
            $update
                ->table($table)
                ->source($this->source())
                ->values($values)
            ;

            $pks = $this->pkArray();
            foreach ($pks as $pk => $value) {
                $update->equal($pk, $value);
            }

            $update->execute();

            $this->data($this->data());

            return $this;
            break;
        default:
            $assert->exception("Unrecognised state of entity.");
            break;
        }
    }

    /**
     * Remove entity attribute data.
     *
     * @param string $name
     * @return self
     */
    public function remove($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }

        if (isset($this->dataModified[$name])) {
            unset($this->dataModified[$name]);
        }

        return $this;
    }

    /**
     * Check if entity data attribute exists.
     *
     * @param string $name Name of attribute
     * @return bool
     */
    public function has($name)
    {
        $data = $this->data();
        $index = array_keys($data);

        if (in_array($name, $index)) {
            return true;
        }

        return false;
    }

    /**
     * Remove all changes made on entity.
     *
     * @param array $fields List of fields you would like to revert.
     * @return self
     */
    public function undo($fields = array())
    {
        if (!empty($fields)) {
            foreach ($fields as $field) {
                unset($this->dataModified[$field]);
            }
        }else{
            $this->dataModified = array();
        }

        return $this;
    }

    /**
     * Update entity state with database.
     *
     * @param array $fields List of fields to update.
     * @return self
     */
    public function update($fields = array())
    {
        $assert = $this->service('assert');

        $select = $this->select();

        $pkDefinition = $this->pkDefinition();

        foreach ($pkDefinition as $pk) {
            $select->equal($pk, $this->get($pk));
        }

        $fetchedData = $select->fetchRow();
        $assert->notNull($fetchedData, "Can't update entity, the entity is deleted from database.");

        $data = array();

        if (!empty($fields)) {
            $data = $this->data();
            foreach ($fields as $field) {
                $data[$field] = $fetchedData[$field];
            }
        }else{
            $data = $fetchedData;

        }

        $this->data($data);

        return $this;
    }

    public function delete()
    {
        $delete = $this->adapter()->dml(MidataDML::DELETE);
        $delete
            ->table($this->table())
            ->source($this->source())
        ;

        $pks = $this->pkArray();
        foreach ($pks as $pk => $value) {
            $delete->equal($pk, $value);
        }

        $delete->execute();

        $this->state(self::STATE_DELETED);

        return $this;
    }
}
