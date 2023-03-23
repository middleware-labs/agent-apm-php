# Getting Started

### agent-apm-php
Description: Agent APM for PHP

### Guides
To use this APM agent, you need to follow below steps:
1. Run `code composer require middleware/agent-apm-php` in your project directory.
2. After successful installation, you need to add `require 'vendor/autoload.php';` in your file.
3. Then after, you need to add `use Middleware\AgentApmPhp\MwApmCollector;` line.
4. Now, you need to add following code in your desired function of any class. This will collect the data, Like: called ClassName, FunctionName, TargetFile and send it to the Middleware Dashboard:

    ```
    $mwCollector = new MwApmCollector('<PROJECT-NAME>', '<SERVICE-NAME>');

    $mwCollector->tracingCall(get_called_class(), __FUNCTION__, __FILE__);
    ```
5. You can add many other attributes as defined [here](https://opentelemetry.io/docs/reference/specification/trace/semantic_conventions/span-general/), or you can add your own custom attributes to the above code, like: 
    
    ```
    $mwCollector->tracingCall(get_called_class(), __FUNCTION__, __FILE__, [
         'code.lineno' => '10',
         'code.column' => 12,
         'net.host.name' => 'localhost',
         'db.name' => 'users',
         'custom.attr1' => 'value1',
    ]);
    ```
### Sample Code
```
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

        $mwCollector = new MwApmCollector('DemoProject', 'PrintService');
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