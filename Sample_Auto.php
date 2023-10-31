<?php
require 'vendor/autoload.php';

/**
* Auto Instrument example.
* Note: Only non static functions can be autoinstrumented.
*/

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

$demo = new DemoClassAuto();
$demo->runCode();

$tracker->postTrack();