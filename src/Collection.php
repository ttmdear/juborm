<?php
namespace Juborm;

use Juborm\Entity;
use Countable;
use Iterator;
use Juborm\ORM;

class Collection extends ORM implements Countable, Iterator
{
    private $position = 0;
    private $data = array();
    private $cleanEntity;
    private $entity;
    private $source;
    private $entities = array();

    private $indexes = array();
    private $indexed = array();

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this->get($this->position);
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
        if ($this->count() == 0) {
            return false;
        }

        if (is_null($this->get($this->position))) {
            return false;
        }

        return true;
    }

    public function save()
    {
        foreach ($this as $entity) {
            $entity->save();
        }

        return $this;
    }

    public function delete()
    {
        foreach ($this as $entity) {
            $entity->delete();
        }

        return $this;
    }

    public function set($name, $value)
    {
        foreach ($this as $entity) {
            $entity->set($name, $value);
        }

        return $this;

    }

    /**
     * Return or set collection data. If you set net date, than you should
     * create indexes again.
     *
     * @param array $data
     */
    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }

        $assert = $this->service('assert');
        $assert->isArray($data);

        $this->data = $data;

        // czyszcze rowniez utworzone indexy aby zostaly na nowo utworzone dla
        // nowych danych
        $this->indexed = array();

        return $this;
    }

    /**
     * Return or set source of collection. Source is set to each entity at
     * collection.
     *
     * @param string $source
     */
    public function source($source = null)
    {
        if (is_null($source)) {
            return $this->source;
        }

        $this->source = $source;

        return $this;
    }

    /**
     * Return the amount of entities at collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Method create index in collection. For indexes is possible to use method
     * like findByPk or findByGroup.
     *
     * @todo Add other type of indexes
     *
     * @example "
     *     $collection->indexes(array(
     *         'PRIMARY' => array('book_id', 'author_id'),
     *         'GROUPS' => function($row){
     *             if($row['type'] == '1'){
     *                 return 'active';
     *             }else{
     *                 return 'closed';
     *             }
     *         }
     *     ));
     * "
     * @param array $indexes Definition of indexes
     * @return self
     */
    public function indexes($indexes = null)
    {

        if (is_null($indexes)) {
            return $this->indexes;
        }

        $this->indexes = $indexes;

        return $this;
    }

    /**
     * Return collection of entity belong to spefic group.
     *
     * @param string $group Name of group used at index.
     * @return Juborm\Collection
     */
    public function findByGroup($group)
    {
        $this->buildIndex();

        $assert = $this->service('assert');
        $assert->hasIndex($this->indexed, 'GROUPS', "To use findByGroup create GROUPS index at collection.");

        if (!isset($this->indexed['GROUPS'][$group])) {
            return array();
        }

        $indexes = $this->indexed['GROUPS'][$group];

        return $this->collection($indexes);
    }

    private function collection($indexes)
    {
        $collection = new self();
        $collection
            ->data($this->entitySet($indexes))
            ->entity($this->entity)
            ->indexes($this->indexes)
            ->source($this->source())
        ;

        return $collection;
    }

    private function entitySet($indexes)
    {
        $set = array();

        foreach ($indexes as $index) {
            $set[] = $this->data[$index];
            //$set[] = $this->get($index);
        }

        return $set;
    }

    /**
     * Set entity class which will be use to create entities.
     * Or return presents entity.
     *
     * @param string $entity
     * @return self
     */
    public function entity($entity = null)
    {
        if (is_null($entity)) {
            return $this->entity;
        }

        $assert = $this->service('assert');
        $assert->isString($entity, "Entity should be string.");

        $this->entity = $entity;
        $this->cleanEntity = new $entity;

        return $this;
    }

    /**
     * Find entity by her primary key. Collection use PRIMARY index to find
     * entity.
     *
     * @param string|array $pkValues If entity has complex key, use array. If
     * entity has simple key use direct value.
     * @return \Juborm\Entity|null
     */
    public function findByPk($pkValues)
    {
        $this->buildIndex();

        $assert = $this->service('assert');
        $assert->hasIndex($this->indexed, 'PRIMARY', "To use findByPk create PRIMARY index at collection.");

        if (!is_array($pkValues)) {
            $pkValues = array($pkValues);
        }

        $toHash = "";
        foreach ($pkValues as $value) {
            $toHash .= $value;
        }

        if (isset($this->indexed['PRIMARY'][md5($toHash)])) {
            $index = $this->indexed['PRIMARY'][md5($toHash)];
            return $this->get($index);
        }else{
            return null;
        }
    }

    /**
     * Return entity by index in directly from data.
     *
     * @param in|string $index
     */
    public function get($index)
    {
        $assert = $this->service('assert');

        if (!isset($this->data[$index])) {
            return null;
        }

        if (isset($this->entities[$index])) {
            return $this->entities[$index];
        }

        if (is_null($this->cleanEntity)) {
            $assert->exception("Can't create entity.");
        }

        $entity = clone($this->cleanEntity);
        $entity->state(Entity::STATE_FETCHED);
        $entity->source($this->source);
        $entity->data($this->data[$index]);

        $this->entities[$index] = $entity;

        return $entity;
    }

    /**
     * Return firt entity of collection or null.
     *
     * @return \Juborm\Entity|null
     */
    public function first()
    {
        if ($this->count() == 0) {
            return null;
        }

        return $this->get(0);
    }

    private function buildIndex()
    {
        if (!empty($this->indexed)) {
            // sa juz utworzone indexy
            return;
        }

        if (empty($this->indexes)) {
            // nie ma podanych indexow
            return;
        }

        $assert = $this->service('assert');
        $indexes = $this->indexes;

        foreach ($this->data as $i => $row) {
            if (isset($indexes['PRIMARY'])) {
                if (!isset($this->indexed['PRIMARY'])) {
                    // nie ma jeszcze arraya na ten index
                    $this->indexed['PRIMARY'] = array();
                }

                // pobieram definicje indexu
                $indexDef = $indexes['PRIMARY'];

                // definicja powinna byc arrayem
                $assert->isArray($indexDef, "Definition of PRIMARY index should be list of columns.");

                $toHash = "";
                foreach ($indexDef as $key) {
                    $assert->hasIndex($row, $key, "Can't create index for row ");
                    $toHash .= $row[$key];
                }

                $this->indexed['PRIMARY'][md5($toHash)] = $i;
            }

            if (isset($indexes['GROUPS'])) {
                // tworzymu index grupowania

                if (!isset($this->indexed['GROUPS'])) {
                    $this->indexed['GROUPS'] = array();
                }

                $grouper = $indexes['GROUPS'];
                $assert->isCallable($grouper, 'GROUPS index should be callback.');

                $grouper = $indexes['GROUPS'];
                $group = call_user_func($grouper, $row);

                if (!isset($this->indexed['GROUPS'][$group])) {
                    $this->indexed['GROUPS'][$group] = array();
                }

                $this->indexed['GROUPS'][$group][] = $i;
            }
        }

        return $this;
    }

}
