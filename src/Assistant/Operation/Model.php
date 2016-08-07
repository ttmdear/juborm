<?php
namespace Juborm\Assistant\Operation;

use Juborm\Assistant\Operation;
use Juborm\Assistant\Template;

class Model extends Operation
{
    public function generate()
    {
        $assert = $this->service('assert');
        $config = $this->service('config');
        $paths = $this->service('paths');
        $paths->setModel($this->model());

        $namespace = $config->get('models.#1.namespace', null, array($this->model()));

        // pobieram adapter dla danego modelu
        // zakladam ze kazdy model ma zrodlo o takiej samej nazwie
        $source = $this->service('sources')->get($this->model());

        $dir = $paths->dir();
        $entities = $paths->entities();
        $baseEntities = $paths->baseEntities();
        $selects = $paths->selects();
        $modelEntity = $paths->modelEntity();
        $modelSelect = $paths->modelSelect();
        $mainEntity = $config->get('models.#1.mainEntity', '\Juborm\Entity', array($this->model()));
        $mainSelect = $config->get('models.#1.mainSelect', '\Juborm\Statement\Select', array($this->model()));
        $generateSelects = $config->get('models.#1.generateSelects', true, array($this->model()));
        $genMapOfClasses = $paths->genMapOfClasses();
        $mapOfClasses = $paths->mapOfClasses();

        $assert->classExists($mainEntity, 'The mainEntity class do not exists.');
        $assert->classExists($mainSelect, 'The mainSelect class do not exists.');
        $assert->isBoolean($generateSelects, 'generateSelects at config should be boolean value (true|false)');

        // tworze katalogi
        $this->mkdir($dir);
        $this->mkdir($entities);
        $this->mkdir($selects);
        $this->mkdir($baseEntities);

        if (!file_exists($modelEntity)) {
            // tworze podstawowa klase encji
            $template = Template::factory('modelEntity');
            $template
                ->bind('namespace', $namespace)
                ->bind('mainEntity', $mainEntity)
                ->bind('model', $this->model())
            ;

            $template->save($modelEntity);
        }

        $tables = $source->tables();
        $map = array();

        foreach ($tables as $table) {
            $table = $source->table($table);
            $tableName = $table->name();
            $columns = $table->columns();

            // przepisuje kolumny na tabele
            $tmpColumns = array();
            foreach ($columns as $column) {
                $tmpColumns[$column] = array();
            }

            $columns = $tmpColumns;

            $primaryKey = $table->primaryKey();
            $className = $this->toCamelCase($tableName);

            $baseEntity = $paths->baseEntity($className);
            $template = Template::factory('baseEntity');

            $template
                ->bind('namespace', "$namespace\Entities\Base")
                ->bind('className', $className)
                ->bind('modelEntity', "\\$namespace\\Entity")
                ->bind('table', $tableName)
                ->bind('fields', $columns)
                ->bind('pk', $primaryKey)
            ;

            if ($generateSelects) {
                $template->bind('select', "$namespace\\Selects\\$className");
            }

            $template->save($baseEntity);

            // entity
            $entity = $paths->entity($className);

            if (!file_exists($entity)) {
                $template = Template::factory('entity');

                $template
                    ->bind('namespace', "$namespace\Entities")
                    ->bind('className', $className)
                    ->bind('baseEntity', "\\$namespace\\Entities\\Base\\$className")
                ;

                $template->save($entity);

            }

            // zapisuje encje
            $map['entities'][$tableName] = "$namespace\\Entities\\".$className;

            if ($generateSelects) {
                if (!file_exists($modelSelect)) {
                    $template = Template::factory('modelSelect');
                    $template
                        ->bind('namespace', $namespace)
                        ->bind('mainSelect', $mainSelect)
                    ;

                    $template->save($modelSelect);
                }

                $select = $paths->select($className);

                if (!file_exists($select)) {
                    $template = Template::factory('select');

                    $template
                        ->bind('namespace', "$namespace\Selects")
                        ->bind('className', $className)
                        ->bind('modelSelect', "\\$namespace\\Select");
                    ;

                    $template->save($select);
                }
            }
        }

        // mapOfClasses
        $template = Template::factory('genMapOfClasses');
        $template->bind('map', $map);
        $template->save($genMapOfClasses);

        if (!file_exists($mapOfClasses)) {
            $template = Template::factory('mapOfClasses');
            $template->save($mapOfClasses);
        }

        // relations
        $genRelations = $paths->genRelations();
        $relations = $paths->relations();
        $definedRelations = array();

        foreach ($tables as $table) {
            $table = $source->table($table);
            $tableName = $table->name();
            $constraints = $table->constraints();

            foreach ($constraints as $constraint) {
                $constraint = $table->constraint($constraint);

                if (!$constraint->isForeignKey()) {
                    // interesuja mnie tylko klucze obce
                    continue;
                }

                $condition = "";

                $columns = $constraint->columns();
                $baseTable = $constraint->baseTable();
                $refTable = $constraint->refTable();
                $constraintName = $constraint->name();
                $path = "$baseTable:$refTable\$$constraintName";

                foreach ($columns as $definition) {
                    $baseColum = $definition['base'];
                    $refColumn = $definition['ref'];

                    $condition .= " AND $baseTable.$baseColum = $refTable.$refColumn";
                }

                $condition = ltrim($condition, ' AND ');

                $definedRelations[$path] = $condition;
            }
        }

        $template = Template::factory('genRelations');
        $template->bind('relations', $definedRelations);
        $template->save($genRelations);

        if(!file_exists($relations)){
            $template = Template::factory('relations');
            $template->save($relations);
        }
    }

    private function toCamelCase($table)
    {
        $table[0] = strtoupper($table[0]);

        $func = create_function('$c', 'return strtoupper($c[1]);');

        return preg_replace_callback('/_([a-z])/', $func, $table);
    }
}

