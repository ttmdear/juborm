<?php
require __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Console\Application;
use Juborm\Assistant\Command\GenerateModel;
use Juborm\Assistant\Command\GenerateRelationsTree;

$application = new Application();
$application->add(new GenerateModel());
$application->add(new GenerateRelationsTree());
$application->run();
