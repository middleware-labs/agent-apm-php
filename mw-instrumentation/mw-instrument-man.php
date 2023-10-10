<?php

use Middleware\AgentApmPhp\MwTracker;

$tracker = new MwTracker('DemoProject', 'PrintService');

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
