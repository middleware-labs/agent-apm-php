<?php
declare(strict_types=1);
namespace Middleware\AgentApmPhp;

require 'vendor/autoload.php';

use DateTime;
use OpenTelemetry\Context\Context;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Trace\Span;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\SemConv\TraceAttributes;

use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor;

use OpenTelemetry\API\Instrumentation;
use function OpenTelemetry\Instrumentation\hook;
use Throwable;

final class MwTracker {

    private string $host = 'localhost';
    private int $exportPort = 9320;
    private string $projectName;
    private string $serviceName;

    private TracerInterface $tracer;
    private Instrumentation\Configurator $scope;
    private TracerProvider $tracerProvider;

    private EventLogger $logger;

    public function __construct(string $projectName = null, string $serviceName = null) {

        if (!empty(getenv('MW_AGENT_SERVICE'))) {
            $this->host = getenv('MW_AGENT_SERVICE');
        }

        if (empty($projectName)) {
            $projectName = 'Project-'. getmypid();
        }

        if (empty($serviceName)) {
            $serviceName = 'Service-'. getmypid();
        }

        $this->projectName = $projectName;
        $this->serviceName = $serviceName;

        // ------ Things for Trace Connection ------

        $traceTransport = (new OtlpHttpTransportFactory())->create(
            'http://' . $this->host . ':' . $this->exportPort . '/v1/traces',
            'application/x-protobuf');

        $traceExporter = new SpanExporter($traceTransport);

        $tracerProvider = new TracerProvider(
            new SimpleSpanProcessor($traceExporter),
            null,
            ResourceInfo::create(Attributes::create([
                'project.name' => $projectName,
                ResourceAttributes::SERVICE_NAME => $serviceName,
                Variables::OTEL_PHP_AUTOLOAD_ENABLED => true
            ]))
        );

        $tracer = $tracerProvider->getTracer('middleware/agent-apm-php', 'dev-master');

        $scope = Instrumentation\Configurator::create()
            ->withTracerProvider($tracerProvider);

        $this->tracerProvider = $tracerProvider;
        $this->tracer = $tracer;
        $this->scope = $scope;

        // ------ Things for Log Connection ------

        $logTransport = (new OtlpHttpTransportFactory())->create(
            'http://' . $this->host . ':' . $this->exportPort . '/v1/logs',
            'application/x-protobuf');

        $logExporter = new LogsExporter($logTransport);

        $loggerProvider = new LoggerProvider(
            new SimpleLogsProcessor($logExporter),
            new InstrumentationScopeFactory(
                (new LogRecordLimitsBuilder())->build()->getAttributeFactory()
            ),
            ResourceInfo::create(Attributes::create([
                'project.name' => $projectName,
                ResourceAttributes::SERVICE_NAME => $serviceName,
                Variables::OTEL_PHP_AUTOLOAD_ENABLED => true,
            ]))
        );

        $this->logger = new EventLogger($loggerProvider->getLogger('middleware/agent-apm-php', 'dev-master'), 'middleware-apm-domain');

    }

    public function registerHook(string $className, string $functionName, ?iterable $attributes = null): void {
        $tracer = $this->tracer;
        $serviceName = $this->serviceName;
        $projectName = $this->projectName;

        hook(
            $className,
            $functionName,
            static function ($object, ?array $params, ?string $class, string $function, ?string $filename, ?int $lineno) use ($tracer, $serviceName, $projectName, $attributes) {
                $span = $tracer->spanBuilder(sprintf('%s::%s', $class, $function))
                    ->setAttribute('service.name', $serviceName)
                    ->setAttribute('project.name', $projectName)
                    ->setAttribute('code.function', $function)
                    ->setAttribute('code.namespace', $class)
                    ->setAttribute('code.filepath', $filename)
                    ->setAttribute('code.lineno', $lineno);

                if (!empty($attributes)) {
                    foreach ($attributes as $key => $value) {
                        $span->setAttribute($key, $value);
                    }
                }

                if (!empty($params)) {

                    // echo $function . PHP_EOL;
                    // print_r($params);
                    switch ($function) {
                        case 'curl_init':
                            isset($params[0]) && $span->setAttribute('code.params.uri', $params[0]);

                            break;
                        case 'curl_exec':
                            $span->setAttribute('code.params.curl', $params[0]);

                            break;

                        case 'fopen':
                            $span->setAttribute('code.params.filename', $params[0])
                                ->setAttribute('code.params.mode', $params[1]);

                            break;
                        case 'fwrite':
                            $span->setAttribute('code.params.file', $params[0])
                                ->setAttribute('code.params.data', $params[1]);

                            break;
                        case 'fread':
                            $span->setAttribute('code.params.file', $params[0])
                                ->setAttribute('code.params.length', $params[1]);

                            break;

                        case 'file_get_contents':
                        case 'file_put_contents':
                        $span->setAttribute('code.params.filename', $params[0]);

                            break;
                    }
                }

                $span = $span->startSpan();
                Context::storage()->attach($span->storeInContext(Context::getCurrent()));
            },
            static function ($object, ?array $params, mixed $return, ?Throwable $exception) use ($tracer) {
                if (!$scope = Context::storage()->scope()) {
                    return;
                }
                $scope->detach();
                $span = Span::fromContext($scope->context());
                if ($exception) {
                    $span->recordException($exception, [TraceAttributes::EXCEPTION_ESCAPED => true]);
                    $span->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
                } else {
                    $span->setStatus(StatusCode::STATUS_OK);
                }
                // $exception && $span->recordException($exception);
                // $span->setStatus($exception ? StatusCode::STATUS_ERROR : StatusCode::STATUS_OK);
                $span->end();
            }
        );
    }

    public function preTrack(): void {
        $this->scope->activate();

        // these will support in php8.2 version.
        // $this->registerHook('fopen', 'fopen');
        // $this->registerHook('fwrite', 'fwrite');
        // $this->registerHook('fread', 'fread');
        // $this->registerHook('file_get_contents', 'file_get_contents');
        // $this->registerHook('file_put_contents', 'file_put_contents');
        // $this->registerHook('curl_init', 'curl_init');
        // $this->registerHook('curl_exec', 'curl_exec');
    }

    public function postTrack(): void {
        if (!$scope = Context::storage()->scope()) {
            return;
        }
        $scope->detach();
        $this->tracerProvider->shutdown();
    }

    private function logging(string $type, int $number, string $text): void {

        $traceId = '';
        $spanId = '';
        if ($scope = Context::storage()->scope()) {
            $span = Span::fromContext($scope->context());
            $spanContext = $span->getContext();
            $traceId = $spanContext->getTraceId();
            $spanId = $spanContext->getSpanId();
        }

        $timestamp = (new DateTime())->getTimestamp() * LogRecord::NANOS_PER_SECOND;

        $logRecord = (new LogRecord($text))
            ->setTimestamp($timestamp)
            ->setObservedTimestamp($timestamp)
            ->setSeverityText($type)
            ->setSeverityNumber($number)
            ->setAttributes([
                'project.name' => $this->projectName,
                'service.name' => $this->serviceName,
                'mw.app.lang' => 'php',
                'fluent.tag' => 'php.app',
                'level' => strtolower($type),
                'trace_id' => $traceId != 0 ? $traceId : '',
                'span_id' => $spanId != 0 ? $spanId : '',
            ]);

        $this->logger->logEvent(
            'logger',
            $logRecord
        );
    }

    public function debug(string $message): void {
        $this->logging('DEBUG', 5, $message);
    }

    public function info(string $message): void {
        $this->logging('INFO', 9, $message);
    }

    public function warn(string $message): void {
        $this->logging('WARN', 13, $message);
    }

    public function error(string $message): void {
        $this->logging('ERROR', 17, $message);
    }

}