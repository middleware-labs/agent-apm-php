<?php
require 'vendor/autoload.php';
require "MwTracker.php";

use Middleware\AgentApmPhp\MwTracker;

/**
* Mannual Instrument example.
*/

$tracker = new MwTracker('DemoProject', 'PrintService');
$tracker->preTrack();
$tracker->registerHook('DemoClass', 'runCode', [
    'code.column' => '12',
    'net.host.name' => 'localhost',
    'db.name' => 'users',
    'custom.attr10' => 'value10',
]);
$tracker->registerHook('DoThings', 'printString', ['code.column' => '12']);

$tracker->warn("this is warning log.");
$tracker->error("this is error log.");
$tracker->info("this is info log.");
$tracker->debug("this is debug log.");

class DoThings {
    public static function printString($str): void {
        // sleep(1);
        global $tracker;
        $tracker->warn("this is warning log, but from inner function.");

        echo $str . PHP_EOL;
    }
}

class DemoClass {
    public static function runCode(): void {
        DoThings::printString('Welcome to Manually Instrumented Function!');
    }
}

DemoClass::runCode();

$tracker->postTrack();