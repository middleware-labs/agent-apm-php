# Getting Started

### agent-apm-php
Description: Agent APM for PHP

### Prerequisites
* To monitor APM data on dashboard, <a href="https://docs.middleware.io/docs/getting-started" target="_blank">Middleware Host-agent</a> needs to be installed, You can refer <a href="https://github.com/middleware-labs/demo-apm/tree/master/php" target="_blank">this demo project</a> to refer use cases of APM.
* PHP requires at least PHP 8+ and <a href="https://opentelemetry.io/docs/instrumentation/php/automatic/#setup" target="_blank">OpenTelemetry PHP-Extension</a> to run this agent.

### Guides
To use this APM agent, follow below steps:
1. Run `composer require middleware/agent-apm-php` in your project directory.
2. After successful installation, you need to add `require 'vendor/autoload.php';` in your file.
3. Then after, you need to add `use Middleware\AgentApmPhp\MwApmCollector;` line.
4. Now, add following code to the next line with your Project & Service name:
   ```
   $mwCollector = new MwApmCollector('<PROJECT-NAME>', '<SERVICE-NAME>');
   ```
5. Then we have 2 functions, named `preTracing()` & `postTracing()`, your code must be placed between these functions. After preTracing() calls, you need to register your desired classes & functions as follows:
   ```
   $mwCollector->preTracing();
   $mwCollector->registerHook('<CLASS-NAME-1>', '<FUNCTION-NAME-1>');
   $mwCollector->registerHook('<CLASS-NAME-2>', '<FUNCTION-NAME-2>');
   ```
6. You can add your own custom attributes as the third parameter, and checkout many other pre-defined attributes [here](https://opentelemetry.io/docs/reference/specification/trace/semantic_conventions/span-general/). 
   ```
   $mwCollector->registerHook('<CLASS-NAME-1>', '<FUNCTION-NAME-1>', [
       'custom.attr1' => 'value1',
       'custom.attr2' => 'value2',
   ]);
   ``` 
7. At the end, just call `postTracing()` function, which will send all the traces to the Middleware Host-agent.
   ```
   $mwCollector->postTracing();
   ```
8. So, final code snippet will look like as:
   ```
   <?php
   require 'vendor/autoload.php';
   
   use Middleware\AgentApmPhp\MwApmCollector;
   
   $mwCollector = new MwApmCollector('<PROJECT-NAME>', '<SERVICE-NAME>');
   $mwCollector->preTracing();
   $mwCollector->registerHook('<CLASS-NAME-1>', '<FUNCTION-NAME-1>', [
       'custom.attr1' => 'value1',
       'custom.attr2' => 'value2',
   ]);
   $mwCollector->registerHook('<CLASS-NAME-2>', '<FUNCTION-NAME-2>');
   
   // Your code goes here
   
   $mwCollector->postTracing();
   ```


*Note: OTEL collector endpoint for all the traces, will be `http://localhost:9320/v1/traces` by default.*

### Sample Code
```
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
```