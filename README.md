
# Getting Started
**Description**: Agent APM for PHP

## Prerequisites
* To monitor APM data on dashboard, [Middleware Host-agent](https://docs.middleware.io/docs/getting-started) needs to be installed, You can refer [this demo project](https://github.com/middleware-labs/demo-apm/tree/master/php) to refer use cases of APM.
* PHP requires at least PHP 8+ and [OpenTelemetry PHP-Extension](https://opentelemetry.io/docs/instrumentation/php/automatic/#setup ) to run this agent.
 
## Instrumentation

### Inside Source Code
1. Create a new php file named *instrument.php* to add instrumentation code.
2. Add mentioned line to your code at start.
```php
<?php
		require  'vendor/autoload.php';
		include  "mw-instrumentation/mw-instrument-man.php";
```
3. At end of the complete script you need to add mentioned line.
```php
 $tracker->postTrack();
```
4. So, final code snippet will look like as:
```php
<?php
		require  'vendor/autoload.php';
		include  "mw-instrumentation/mw-instrument-man.php";
		<YOUR CODE>
		$tracker->postTrack();
>
```
### Instrumentation
In instrumentation file that you just created you can need to add some common code irrespective of whether you choose auto instrumentation or manual.
```php
<?php
use Middleware\AgentApmPhp\MwTracker;
$tracker = new  MwTracker('<PROJECT_NAME>', '<SERVICE_NAME>');
```
- **Auto Instrumentation**
If you want to automatically track the performance and behavior of a function, you can do so by adding the following code to it:
```php
$tracker->instrumentFunction(<CLASS_NAME>::class,"<FUNCTION_NAME>");
```
You can also use custom attributes to instrument functions. This allows you to specify additional information about the function, such as its purpose, or dependencies.
```php
$tracker->instrumentFunction(<CLASS_NAME>::class,"<FUNCTION_NAME>",[
		'custom.attr1' => 'custom.val1',
		'custom.attr2' => 'custom.val2',
]);
```
***NOTE***: Only non static functions can be auto instrumented. 

- **Manual Instrumentation**
To manually instrument a function, register a hook using the following function:
```php
$tracker->registerHook('<CLASS_NAME>', '<FUNCTION_NAME>');
```
Similar to auto instrumentation we can use custom attributes here as well:
```php
$tracker->registerHook('<CLASS_NAME>', '<FUNCTION_NAME>',[
		'custom.attr1' => 'custom.val1',
		'custom.attr2' => 'custom.val2',
]);
```
## Logging
Custom logs are also available. You can use them by calling the mentioned function for different types of logs.
```php
$tracker->warn("<CUSTOM_MESSAGE>"); 	//Warning Log
$tracker->error("<CUSTOM_MESSAGE>"); 	//Error Log
$tracker->info("<CUSTOM_MESSAGE>"); 	//Info Log
$tracker->debug("<CUSTOM_MESSAGE>"); 	//Debug Log
```
You can use these functions throughout your code to generate meaningful logs. This will help you track and debug your code more effectively.

