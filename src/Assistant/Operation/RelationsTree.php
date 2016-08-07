<?php
namespace Juborm\Assistant\Operation;

use Juborm\Assistant\Operation;

class RelationsTree extends Operation
{
    private $centralEntities;

    public function generate()
    {
        $assert = $this->service('assert');
        $config = $this->service('config');
        $paths = $this->service('paths');
        $paths->setModel($this->model());

        $relationsTree = $paths->relationsTree();
        $relationsTreeIndex = $paths->relationsTreeIndex();
        $relations = $paths->relations();
        $genRelations = $paths->genRelations();

        $relationsArray = array();

        if (file_exists($genRelations)) {
            $tmp = include($genRelations);
            $relationsArray = array_merge($relationsArray, $tmp);
        }

        if (file_exists($relations)) {
            $tmp = include($relations);
            $relationsArray = array_merge($relationsArray, $tmp);
        }

        if (!is_array($relationsArray)) {
            $assert->exception("Relations should be defined as array.");
        }

        if(empty($relationsArray)){
            $assert->exception("There are no relations.");
        }

        // tworze katalog na relacje
        $this->mkdir($relationsTree);

        // indexed entities
        $entities = array();

        foreach ($relationsArray as $relation => $condition) {
            $exploded = explode(':', $relation);

            if (!in_array(count($exploded), array(2,3))) {
                throw new Exception("Each relation should be defined like tableA:tableB\$pathName => .... Path is optionaly");
            }

            $nameA = $exploded[0];

            $exploded = explode('$', $exploded[1]);
            $nameB = $exploded[0];
            $pathName = isset($exploded[1]) ? $exploded[1] : null;

            $entityA = isset($entities[$nameA]) ? $entities[$nameA] : new Entity($nameA);
            $entityB = isset($entities[$nameB]) ? $entities[$nameB] : new Entity($nameB);

            // zapisuje encje do indexow
            $entities[$nameA] = $entityA;
            $entities[$nameB] = $entityB;

            $entityA->addNeighbor($entityB, $condition, $pathName);
            $entityB->addNeighbor($entityA, $condition, $pathName);
        }

        $joins = array();

        $centralEntities = $config->get('models.#1.centralEntities', null, array($this->model()));

        $this->centralEntities = array();
        if (!is_null($centralEntities)) {
            $centralEntities = explode(',', $centralEntities);
            foreach ($centralEntities as &$centralEntity) {
                $centralEntity = trim($centralEntity);
            }

            $this->centralEntities = $centralEntities;
        }

        // teraz musze utworzyc drzewo wszystkich relacji
        foreach ($entities as $name => $entity) {
            if (in_array($name, $this->centralEntities)) {
                continue;
            }

            $entityPath = "$name";

            // zaznaczam ze odwiedzilem encje dana sciezka
            $visitedEntity = array($entity);

            $this->travers($entity, $visitedEntity, $joins, $entityPath, array(), $entity);
        }

        $relationIndex = array();
        $relationIndex = new RelationsIndex();

        foreach ($joins as $relation => $join) {
            // $tmpJoin = array();
            // foreach ($join as $table => $condition) {
            //     $table = explode("$", $table);
            //     $tmpJoin[$table[0]] = $condition;
            // }

            // $join = $tmpJoin;

            // zapisujemy plik
            $hash = md5($relation);
            $fileName = $relationsTree.'/'.$hash;
            file_put_contents($fileName, serialize($join));

            // tworzymy teraz roznego rodzaju odwolania do tego pliku
            // odwolanie bezposrednie za pomoca calej sciezki
            $relationIndex->add($relation, $hash);

            $withoutPaths = $this->withoutPaths($relation);

            $relationIndex->add($withoutPaths, $hash);

            $relationIndex->add($this->lastAndFirst($withoutPaths), $hash);
            $relationIndex->add($this->lastAndFirst($relation), $hash);

        }

        $relationIndex = $relationIndex->exportToString();

        $indexedFile = "<?php
        return $relationIndex;
        ";

        file_put_contents($relationsTreeIndex, $indexedFile);
    }

    protected function withoutPaths($relation)
    {
        // usuwam informacje o poszczegolnych sciezkach miedzy encjami
        $reg = "/(\\$.*?):/";
        $withoutPaths = preg_replace($reg, ":", $relation);

        $reg = "/(\\$.*?)$/";
        $withoutPaths = preg_replace($reg, "", $withoutPaths);

        return $withoutPaths;
    }

    protected function lastAndFirst($relation)
    {
        $exploded = explode(":", $relation);

        $first = $exploded[0];
        $last = $exploded[count($exploded)-1];
        return "$first:$last";
    }

    public function travers($parentEntity, $visitedEntity, &$joins, $entityPath, $relationCondition, $rootEntity)
    {
        // $localNeighbors przetrzymuje lokalnych sasiadow , chodzi o to aby
        // moc uwzglednic kilka relacji miedzy dwiema sciezkami
        // np. relacja z tabela slownikowa gdzie mamy kilka kolumn ktore moga
        // laczyc sie ze slownikiem
        // czyli moge miec relacje takie jak :
        // authors_addresses -> dictionary_values (dla country)
        // authors_addresses -> dictionary_values (dla street_prefix)
        // authors_addresses -> dictionary_values (dla type)
        //
        // Wszystkie relacje rozni Path okreslany przez
        // authors_addresses:dictionary_values$country
        // authors_addresses:dictionary_values$street_prefix
        // authors_addresses:dictionary_values$type
        $localNeighbors = array();

        foreach ($parentEntity->neighbors() as $neighbor) {
            $neighborCondition = $relationCondition;

            $entity = $neighbor['entity'];
            $entityName = $entity->getName();
            $condition = $neighbor['condition'];
            $pathName = $neighbor['pathName'];

            if(in_array($entity, $visitedEntity)){
                if(!in_array($entity, $localNeighbors)){
                    continue;
                }
            }

            $visitedEntity[] = $entity;
            $localNeighbors[] = $entity;

            $relationPath = "$entityPath:$entityName";

            if (!is_null($pathName)) {
                // dodajemy pathName
                $relationPath = "$entityPath\$$pathName:$entityName";
            }

            //$relationPath = $entityPath . ":".$pathName;
            $neighborCondition[$entity->getName()] = $condition;

            $joins[$relationPath] = $neighborCondition;

            // jesli nasza encja jest tgz. encja centralna z ktora laczy sie
            // wiele innych encji . Przykladem takiej encji moze byc
            // dictionary_values czyli encja slownikowa, to aby uniknac
            // tworzenia zbednych sciezek miedzy encjami koncze traversowanie
            // jak dojde do takiej encji, oczywiscie encje centralne musi
            // zdefiniowac uzytkownik
            if (in_array($entity->getName(), $this->centralEntities)) {
                continue;
            }

            $this->travers($entity, $visitedEntity, $joins, $relationPath, $neighborCondition, $rootEntity);
        }
    }
}

class RelationsIndex
{
    protected $index = array();

    public function add($name, $hash)
    {
        if (!isset($this->index[$name])) {
            $this->index[$name] = array();
        }

        if(!in_array($hash, $this->index[$name])){
            $this->index[$name][] = $hash;
        }

        return;
    }

    public function exportToString()
    {
        return var_export($this->index, true);
    }
}

class Entity
{
    protected $name;
    protected $neighbors = array();
    protected $indexed = array();

    function __construct($name)
    {
        $this->name = $name;
    }

    public function neighbors()
    {
        return $this->neighbors;
    }

    public function addNeighbor(Entity $entity, $condition, $pathName = null)
    {
        // if (is_null($pathName)) {
        //     $pathName = $entity->getName();
        // }else{
        //     $pathName = $entity->getName()."\$$pathName";
        // }

        $this->indexed[$pathName] = $entity;
        $this->neighbors[] = array(
            'entity' => $entity,
            'condition' => $condition,
            'pathName' => $pathName,
        );

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }
}

