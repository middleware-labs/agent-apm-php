<?php

use Middleware\AgentApmPhp\MwTracker;

$tracker = new MwTracker('DemoProject', 'PrintService');

$tracker->instrumentFunction(DemoClassAuto::class,"runCode",[
    'code.column' => '12',
    'net.host.name' => 'localhost',
    'db.name' => 'users',
    'custom.attr10' => 'value10',
]);
$tracker->instrumentFunction(DoThingsAuto::class,"printString");