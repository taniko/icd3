<?php
require __DIR__ .'/../vendor/autoload.php';
$init = new Hrgruri\Icd3\Setup\Init(__DIR__.'/log/relevant_json');
$init->insertLog();
