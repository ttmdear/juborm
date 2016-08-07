<?php
namespace Juborm;

use Juborm\ORM as ORM;
use Juborm\Model as ORMModel;
use Juborm\Adapter as ORMAdapter;
use Midata\Adapter as MidataAdapter;

/**
 * Manages data sources.
 */
class Sources extends ORM
{
    private $inited = array();

    public function get($source)
    {
        $assert = $this->service('assert');
        $config = $this->service('config');

        if (isset($this->inited[$source])) {
            return $this->inited[$source];
        }

        $config = $config->get("sources.$source");

        $assert->notNull($config,"The config of $source is not defined.");
        $assert->hasIndex($config, 'adapter', 'Each source config should have defined adapter.');

        $adapter = MidataAdapter::factory($config['adapter'], $config, $source);
        $adapter = new ORMAdapter($adapter);

        return $this->inited[$source] = $adapter;
    }
}
