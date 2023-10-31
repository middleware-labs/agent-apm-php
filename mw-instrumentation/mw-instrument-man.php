<?php

use Middleware\AgentApmPhp\MwTracker;

global $tracker;
if($argc >= 3 && in_array($argv[1], array('-c','--config'))){
    if(file_exists($argv[2])){
        $tracker = new MwTracker($argv[2]);
    }else{
        throw new Exception("Config file not found");
    }
}
else{
    if(file_exists('config.ini')){
        echo "No config file in arguments so using default config.ini file.\n";
        $tracker = new MwTracker();
    }else
    throw new Exception("Please provide config file");
}

$tracker->preTrack();
$tracker->registerHook('DemoClass', 'runCode', [
    'code.column' => '12',
    'net.host.name' => 'localhost',
    'db.name' => 'users',
    'custom.attr1' => 'value1',
]);
$tracker->registerHook('DoThings', 'printString');

$tracker->warn("this is warning log.");
$tracker->error("this is error log.");
$tracker->info("this is info log.");
$tracker->debug("this is debug log.");
