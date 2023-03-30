<?php
require 'vendor/autoload.php';

use Middleware\AgentApmPhp\MwApmCollector;

$mwCollector = new MwApmCollector('DemoProject', 'PrintService');
$mwCollector->preTracing();
$mwCollector->registerHook('DemoClass', 'runCode', [
    'code.column' => '12',
    'net.host.name' => 'localhost',
    'db.name' => 'users',
    'custom.attr1' => 'value1',
]);
$mwCollector->registerHook('DoThings', 'printString');

class DoThings {
    public static function printString($str): void {
        // sleep(1);
        echo $str . PHP_EOL;
    }
}

class DemoClass {
    public static function runCode(): void {
        DoThings::printString('Hello World!');
    }
}

DemoClass::runCode();

$mwCollector->postTracing();