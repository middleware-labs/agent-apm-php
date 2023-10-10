<?php
require 'vendor/autoload.php';

include "mw-instrumentation/mw-instrument-man.php";

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