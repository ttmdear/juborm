<?php
// $srcRoot = __DIR__."/../";
// $buildRoot = __DIR__."/../../bin";
// $vendorRoot = __DIR__."/../../vendor";
//
// $phar = new Phar($buildRoot . "/juborm.phar", 0, "juborm.phar");
// $phar->buildFromDirectory("$srcRoot",'/.php$/');
// $files = $phar->buildFromDirectory("$vendorRoot/nategood/commando/src",'/.php$/');
// $files = $phar->buildFromDirectory("$vendorRoot/kevinlebrun/colors.php/lib",'/.php$/');
//
// $phar->setStub($phar->createDefaultStub('Assistant/bootloader.php'));

$srcRoot = __DIR__."/../..";
$buildRoot = __DIR__."/../../bin";

$phar = new Phar($buildRoot . "/juborm.phar", 0, "juborm.phar");
$files = $phar->buildFromDirectory("$srcRoot",'/.php$/');

$phar->setStub($phar->createDefaultStub('/src/Assistant/bootloader.php'));
