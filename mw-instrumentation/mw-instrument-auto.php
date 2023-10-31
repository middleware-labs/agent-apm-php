<?php
use Middleware\AgentApmPhp\MwTracker;

global $tracker;
if($argc >= 3 && in_array($argv[1], array('-c','--config')))
    $tracker = new MwTracker($argv[2]);
else{
    if(file_exists('config.ini')){
        echo "No config file in arguments so using default config.ini file.\n";
        $tracker = new MwTracker();
    }else
    throw new Exception("Please provide config file");
}

$tracker->instrumentFunction(DemoClassAuto::class,"runCode",[
    'code.column' => '12',
    'net.host.name' => 'localhost',
    'db.name' => 'users',
    'custom.attr10' => 'value10',
]);
$tracker->instrumentFunction(DoThingsAuto::class,"printString");