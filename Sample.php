<?php
require 'vendor/autoload.php';
use Middleware\AgentApmPhp\MwApmCollector;

class DoThings {
    public static function printString($str): void {
        echo $str . PHP_EOL;
    }
}

class DemoClass {
    public static function printFunction(): void {

        $mwCollector = new MwApmCollector('DemoProject2', 'PrintService2');
        $mwCollector->tracingCall(get_called_class(), __FUNCTION__, __FILE__, [
            'code.lineno' => '10',
            'code.column' => '12',
            'net.host.name' => 'localhost',
            'db.name' => 'users',
            'custom.attr1' => 'value1',
        ]);

        DoThings::printString('Hello World!');

    }
}

DemoClass::printFunction();