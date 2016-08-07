<?php
namespace Juborm\Tests;

use Juborm\ORM;
use DbDiffer\DbDiffer;

class Base extends \PHPUnit_Framework_TestCase
{
    private $initedDbDiffer = false;

    public function md5($var)
    {
        return md5(var_export($var, true));
    }

    public function inline($var)
    {
        $var = explode("\n", var_export($var,true));
        $inline = "";

        foreach ($var as $element) {
            $element = str_replace(" ", "", $element);
            $inline .= $element;
        }

        return $inline;
    }

    protected function initConnection()
    {
        $config = ORM::service('config');
        $config->load(__DIR__.'/bookstore/src/config/juborm.xml', 'production');
        $config->set("models.bookstore.dir", "./bookstore/src/Model/Bookstore");

        $this->dbDiffer('bookstore')->clean();
        $this->dbDiffer('bookstore_clone')->clean();
    }

    public function dbdiffer($name)
    {
        if (!$this->initedDbDiffer) {
            DbDiffer::loadConfig('./dbdiffer.xml');
            $this->initedDbDiffer = true;
        }

        return DbDiffer::db($name);
    }
}
