<?php
require 'vendor/autoload.php';
require "MwTracker.php";

use Middleware\AgentApmPhp\MwTracker;

/**
* Auto Instrument example.
* Note: Only non static functions can be autoinstrumented.
*/

$tracker = new MwTracker('DemoProject', 'PrintService');

class DoThingsAuto {
    public function printString($str): void {
        // sleep(1);
        echo $str . PHP_EOL;
    }
}
class DemoClassAuto {
    public function runCode(): void {
        $print = new DoThingsAuto();
        $print->printString('Welcome to Auto Instrumented Function!');
    }
}

$tracker->instrumentFunction(DemoClassAuto::class,"runCode",[
    'code.column' => '12',
    'net.host.name' => 'localhost',
    'db.name' => 'users',
    'custom.attr10' => 'value10',
]);
$tracker->instrumentFunction(DoThingsAuto::class,"printString");


$demo = new DemoClassAuto();
$demo->runCode();

$tracker->postTrack();