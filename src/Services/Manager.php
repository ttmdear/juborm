<?php
namespace Juborm\Services;

use Juborm\Services\IniterInterface;
use Exception;

// initers of base services
use Juborm\Services\Config as ConfigIniter;
use Juborm\Services\Sources as SourcesIniter;
use Juborm\Services\Relations as RelationsIniter;
use Juborm\Services\Assert as AssertIniter;
use Juborm\Services\ClassMap as ClassMapIniter;
use Juborm\Services\Paths as PathsIniter;
use Juborm\Services\Util as UtilIniter;

class Manager
{
    private $services = array();

    private $initers = array(
        'config' => ConfigIniter::class,
        'sources' => SourcesIniter::class,
        'relations' => RelationsIniter::class,
        'assert' => AssertIniter::class,
        'classes' => ClassMapIniter::class,
        'paths' => PathsIniter::class,
        'util' => UtilIniter::class,
    );

    /**
     * Define service service.
     *
     * @param string @name Name of service.
     * @param Juborm\Services\IniterInterface|object @initer The class which
     * implements IniterInterface or already created object.
     *
     * @return self
     */
    public function define($name, $initer)
    {
        if ($initer instanceof IniterInterface) {
            // zapisuje initer ktory potem posluzy do uruchomienia uslugi
            $this->initers[$name] = $initer;
        }else{
            // mam bezposrednio podany obiekt
            $this->services[$name] = array(
                'service' => $initer,
                'initer' => null,
            );
        }

        return $this;
    }

    /**
     * Return inited service.
     * @param string @name Name of service to get
     * @return mixed Services object
     */
    public function get($name)
    {
        if (isset($this->services[$name])) {
            // dana usluga zostala juz zainicjowana
            return $this->services[$name]['service'];
        }

        if (!isset($this->initers[$name])) {
            throw new Exception("The service $name is not defined.");
        }

        $initer = $this->initers[$name];
        $initer = new $initer();

        if (!($initer instanceof IniterInterface)) {
            throw new Exception("Initer should be instance of IniterInterface.");
        }

        $initer->booting($this);

        $this->services[$name] = array(
            'service' => $initer->start($this),
            'initer' => $initer
        );

        return $this->get($name);
    }
}
