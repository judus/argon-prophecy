<?php

declare(strict_types=1);

use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class Prophecy extends ArgonContainer
{
	private $tagMap = [
		'service.provider' => [
			'Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation' => [],
			'Maduser\Argon\Prophecy\Provider\ArgonErrorHandlerServiceProvider' => [],
			'Maduser\Argon\Prophecy\Provider\ArgonMessageServiceProvider' => [],
			'Maduser\Argon\Prophecy\Provider\ArgonRequestHandlerServiceProvider' => [],
			'Maduser\Argon\Prophecy\Provider\ArgonMiddlewareServiceProvider' => [],
		],
		'exception.formatter' => ['Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface' => []],
		'exception.handler' => ['Maduser\Argon\Contracts\ErrorHandling\Http\ErrorHandlerInterface' => []],
		'exception.dispatcher' => ['Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionDispatcherInterface' => []],
		'kernel.http' => ['Maduser\Argon\Contracts\KernelInterface' => []],
		'http' => [
			'Psr\Http\Message\ServerRequestFactoryInterface' => [],
			'Psr\Http\Message\ServerRequestInterface' => [],
			'Psr\Http\Message\ResponseFactoryInterface' => [],
			'Psr\Http\Message\ResponseInterface' => [],
			'Psr\Http\Message\StreamFactoryInterface' => [],
			'Psr\Http\Message\StreamInterface' => [],
			'Psr\Http\Message\UriFactoryInterface' => [],
			'Psr\Http\Message\UriInterface' => [],
			'Psr\Http\Message\UploadedFileFactoryInterface' => [],
			'Psr\Http\Message\UploadedFileInterface' => [],
		],
		'psr-17' => [
			'Psr\Http\Message\ServerRequestFactoryInterface' => [],
			'Psr\Http\Message\ResponseFactoryInterface' => [],
			'Psr\Http\Message\StreamFactoryInterface' => [],
			'Psr\Http\Message\UriFactoryInterface' => [],
			'Psr\Http\Message\UploadedFileFactoryInterface' => [],
		],
		'psr-7' => [
			'Psr\Http\Message\ServerRequestInterface' => [],
			'Psr\Http\Message\ResponseInterface' => [],
			'Psr\Http\Message\StreamInterface' => [],
			'Psr\Http\Message\UriInterface' => [],
			'Psr\Http\Message\UploadedFileInterface' => [],
		],
		'request_handler_factory' => ['Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface' => []],
		'middleware.pipeline' => ['Psr\Http\Server\RequestHandlerInterface' => []],
		'psr-15' => ['Psr\Http\Server\RequestHandlerInterface' => []],
		'middleware.http' => [
			'Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface' => [
				'priority' => 6000,
				'group' => ['api', 'web'],
			],
			'Maduser\Argon\Contracts\Http\Server\Middleware\JsonResponderInterface' => [
				'priority' => 5800,
				'group' => ['api', 'web'],
			],
			'Maduser\Argon\Contracts\Http\Server\Middleware\HtmlResponderInterface' => [
				'priority' => 5600,
				'group' => 'web',
			],
			'Maduser\Argon\Contracts\Http\Server\Middleware\PlainTextResponderInterface' => [
				'priority' => 5400,
				'group' => 'web',
			],
			'Maduser\Argon\Contracts\Http\Server\Middleware\ResponseResponderInterface' => [
				'priority' => 5200,
				'group' => ['api', 'web'],
			],
		],
	];

	private $parameters = ['basePath' => '.', 'kernel.debug' => true, 'kernel.shouldExit' => true];
	private $preInterceptors = [];
	private $postInterceptors = [];
	private ?Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation $singleton_get_Maduser_Argon_Prophecy_Provider_ArgonHttpFoundation = null;
	private ?Maduser\Argon\Prophecy\Provider\ArgonErrorHandlerServiceProvider $singleton_get_Maduser_Argon_Prophecy_Provider_ArgonErrorHandlerServiceProvider = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionFormatterInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ErrorHandlerInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionDispatcherInterface = null;
	private ?object $singleton_get_Psr_Log_LoggerInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_ResponseEmitterInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_KernelInterface = null;
	private ?Maduser\Argon\Prophecy\Provider\ArgonMessageServiceProvider $singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMessageServiceProvider = null;
	private ?object $singleton_get_Psr_Http_Message_ServerRequestFactoryInterface = null;
	private ?object $singleton_get_Psr_Http_Message_ServerRequestInterface = null;
	private ?object $singleton_get_Psr_Http_Message_ResponseFactoryInterface = null;
	private ?object $singleton_get_Psr_Http_Message_ResponseInterface = null;
	private ?object $singleton_get_Psr_Http_Message_StreamFactoryInterface = null;
	private ?object $singleton_get_Psr_Http_Message_StreamInterface = null;
	private ?object $singleton_get_Psr_Http_Message_UriFactoryInterface = null;
	private ?object $singleton_get_Psr_Http_Message_UriInterface = null;
	private ?object $singleton_get_Psr_Http_Message_UploadedFileFactoryInterface = null;
	private ?object $singleton_get_Psr_Http_Message_UploadedFileInterface = null;
	private ?Maduser\Argon\Prophecy\Provider\ArgonRequestHandlerServiceProvider $singleton_get_Maduser_Argon_Prophecy_Provider_ArgonRequestHandlerServiceProvider = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_Server_Factory_RequestHandlerFactoryInterface = null;
	private ?object $singleton_get_Psr_Http_Server_RequestHandlerInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_Server_ResultContextInterface = null;
	private ?Maduser\Argon\Prophecy\Provider\ArgonMiddlewareServiceProvider $singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMiddlewareServiceProvider = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_DispatcherInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_JsonResponderInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_HtmlResponderInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_PlainTextResponderInterface = null;
	private ?object $singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_ResponseResponderInterface = null;

	public $serviceMap = [
		'Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation' => 'get_Maduser_Argon_Prophecy_Provider_ArgonHttpFoundation',
		'Maduser\Argon\Prophecy\Provider\ArgonErrorHandlerServiceProvider' => 'get_Maduser_Argon_Prophecy_Provider_ArgonErrorHandlerServiceProvider',
		'Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface' => 'get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionFormatterInterface',
		'Maduser\Argon\Contracts\ErrorHandling\Http\ErrorHandlerInterface' => 'get_Maduser_Argon_Contracts_ErrorHandling_Http_ErrorHandlerInterface',
		'Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionDispatcherInterface' => 'get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionDispatcherInterface',
		'Psr\Log\LoggerInterface' => 'get_Psr_Log_LoggerInterface',
		'Maduser\Argon\Contracts\Http\ResponseEmitterInterface' => 'get_Maduser_Argon_Contracts_Http_ResponseEmitterInterface',
		'Maduser\Argon\Contracts\KernelInterface' => 'get_Maduser_Argon_Contracts_KernelInterface',
		'Maduser\Argon\Prophecy\Provider\ArgonMessageServiceProvider' => 'get_Maduser_Argon_Prophecy_Provider_ArgonMessageServiceProvider',
		'Psr\Http\Message\ServerRequestFactoryInterface' => 'get_Psr_Http_Message_ServerRequestFactoryInterface',
		'Psr\Http\Message\ServerRequestInterface' => 'get_Psr_Http_Message_ServerRequestInterface',
		'Psr\Http\Message\ResponseFactoryInterface' => 'get_Psr_Http_Message_ResponseFactoryInterface',
		'Psr\Http\Message\ResponseInterface' => 'get_Psr_Http_Message_ResponseInterface',
		'Psr\Http\Message\StreamFactoryInterface' => 'get_Psr_Http_Message_StreamFactoryInterface',
		'Psr\Http\Message\StreamInterface' => 'get_Psr_Http_Message_StreamInterface',
		'Psr\Http\Message\UriFactoryInterface' => 'get_Psr_Http_Message_UriFactoryInterface',
		'Psr\Http\Message\UriInterface' => 'get_Psr_Http_Message_UriInterface',
		'Psr\Http\Message\UploadedFileFactoryInterface' => 'get_Psr_Http_Message_UploadedFileFactoryInterface',
		'Psr\Http\Message\UploadedFileInterface' => 'get_Psr_Http_Message_UploadedFileInterface',
		'Maduser\Argon\Prophecy\Provider\ArgonRequestHandlerServiceProvider' => 'get_Maduser_Argon_Prophecy_Provider_ArgonRequestHandlerServiceProvider',
		'Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface' => 'get_Maduser_Argon_Contracts_Http_Server_Factory_RequestHandlerFactoryInterface',
		'Psr\Http\Server\RequestHandlerInterface' => 'get_Psr_Http_Server_RequestHandlerInterface',
		'Maduser\Argon\Contracts\Http\Server\ResultContextInterface' => 'get_Maduser_Argon_Contracts_Http_Server_ResultContextInterface',
		'Maduser\Argon\Prophecy\Provider\ArgonMiddlewareServiceProvider' => 'get_Maduser_Argon_Prophecy_Provider_ArgonMiddlewareServiceProvider',
		'Maduser\Argon\Contracts\Http\Server\Middleware\DispatcherInterface' => 'get_Maduser_Argon_Contracts_Http_Server_Middleware_DispatcherInterface',
		'Maduser\Argon\Contracts\Http\Server\Middleware\JsonResponderInterface' => 'get_Maduser_Argon_Contracts_Http_Server_Middleware_JsonResponderInterface',
		'Maduser\Argon\Contracts\Http\Server\Middleware\HtmlResponderInterface' => 'get_Maduser_Argon_Contracts_Http_Server_Middleware_HtmlResponderInterface',
		'Maduser\Argon\Contracts\Http\Server\Middleware\PlainTextResponderInterface' => 'get_Maduser_Argon_Contracts_Http_Server_Middleware_PlainTextResponderInterface',
		'Maduser\Argon\Contracts\Http\Server\Middleware\ResponseResponderInterface' => 'get_Maduser_Argon_Contracts_Http_Server_Middleware_ResponseResponderInterface',
	];


	public function __construct()
	{
		parent::__construct();
		$this->getParameters()->setStore(array (
		  'basePath' => '.',
		  'kernel.debug' => true,
		  'kernel.shouldExit' => true,
		));
	}


	private function get_Maduser_Argon_Prophecy_Provider_ArgonHttpFoundation(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonHttpFoundation === null) {
		        $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonHttpFoundation = new \Maduser\Argon\Prophecy\Provider\ArgonHttpFoundation();
		    }
		    return $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonHttpFoundation;
	}


	private function get_Maduser_Argon_Prophecy_Provider_ArgonErrorHandlerServiceProvider(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonErrorHandlerServiceProvider === null) {
		        $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonErrorHandlerServiceProvider = new \Maduser\Argon\Prophecy\Provider\ArgonErrorHandlerServiceProvider();
		    }
		    return $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonErrorHandlerServiceProvider;
	}


	private function get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionFormatterInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionFormatterInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionFormatterInterface = new \Maduser\Argon\ErrorHandling\Http\ExceptionFormatter($args['responseFactory'] ?? $this->get('Psr\Http\Message\ResponseFactoryInterface'),
		$args['streamFactory'] ?? $this->get('Psr\Http\Message\StreamFactoryInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? NULL,
		$args['debug'] ?? false ?? false);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionFormatterInterface;
	}


	private function get_Maduser_Argon_Contracts_ErrorHandling_Http_ErrorHandlerInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ErrorHandlerInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ErrorHandlerInterface = new \Maduser\Argon\ErrorHandling\Http\ErrorHandler($args['dispatcher'] ?? $this->get('Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionDispatcherInterface'),
		$args['formatter'] ?? $this->get('Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? 'Psr\\Log\\LoggerInterface' ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ErrorHandlerInterface;
	}


	private function get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionDispatcherInterface(
		array $args = [],
	): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionDispatcherInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionDispatcherInterface = new \Maduser\Argon\ErrorHandling\Http\ExceptionDispatcher($args['formatter'] ?? $this->get('Maduser\Argon\Contracts\ErrorHandling\Http\ExceptionFormatterInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_ErrorHandling_Http_ExceptionDispatcherInterface;
	}


	private function get_Psr_Log_LoggerInterface(array $args = []): object
	{
		if ($this->singleton_get_Psr_Log_LoggerInterface === null) {
		        $this->singleton_get_Psr_Log_LoggerInterface = new \Psr\Log\NullLogger();
		    }
		    return $this->singleton_get_Psr_Log_LoggerInterface;
	}


	private function get_Maduser_Argon_Contracts_Http_ResponseEmitterInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_ResponseEmitterInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_ResponseEmitterInterface = new \Maduser\Argon\Http\ResponseEmitter();
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_ResponseEmitterInterface;
	}


	private function get_Maduser_Argon_Contracts_KernelInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_KernelInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_KernelInterface = new \Maduser\Argon\Http\Kernel($args['exceptionHandler'] ?? $this->get('Maduser\Argon\Contracts\ErrorHandling\Http\ErrorHandlerInterface'),
		$args['request'] ?? $this->get('Psr\Http\Message\ServerRequestInterface'),
		$args['handler'] ?? $this->get('Psr\Http\Server\RequestHandlerInterface'),
		$args['emitter'] ?? $this->get('Maduser\Argon\Contracts\Http\ResponseEmitterInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? 'Psr\\Log\\LoggerInterface' ?? NULL,
		$args['debug'] ?? false ?? false,
		$args['shouldExit'] ?? true ?? true);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_KernelInterface;
	}


	private function get_Maduser_Argon_Prophecy_Provider_ArgonMessageServiceProvider(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMessageServiceProvider === null) {
		        $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMessageServiceProvider = new \Maduser\Argon\Prophecy\Provider\ArgonMessageServiceProvider();
		    }
		    return $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMessageServiceProvider;
	}


	private function get_Psr_Http_Message_ServerRequestFactoryInterface(array $args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_ServerRequestFactoryInterface === null) {
		        $this->singleton_get_Psr_Http_Message_ServerRequestFactoryInterface = new \Maduser\Argon\Http\Message\Factory\ServerRequestFactory();
		    }
		    return $this->singleton_get_Psr_Http_Message_ServerRequestFactoryInterface;
	}


	private function get_Psr_Http_Message_ServerRequestInterface(array &$args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_ServerRequestInterface === null) {
		        $factory = $this->get(\Psr\Http\Message\ServerRequestFactoryInterface::class, $args);
		        $this->singleton_get_Psr_Http_Message_ServerRequestInterface = $factory->__invoke(

		        );
		    }
		    return $this->singleton_get_Psr_Http_Message_ServerRequestInterface;
	}


	private function get_Psr_Http_Message_ResponseFactoryInterface(array $args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_ResponseFactoryInterface === null) {
		        $this->singleton_get_Psr_Http_Message_ResponseFactoryInterface = new \Maduser\Argon\Http\Message\Factory\ResponseFactory($args['streamFactory'] ?? $this->get('Psr\Http\Message\StreamFactoryInterface'));
		    }
		    return $this->singleton_get_Psr_Http_Message_ResponseFactoryInterface;
	}


	private function get_Psr_Http_Message_ResponseInterface(array &$args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_ResponseInterface === null) {
		        $factory = $this->get(\Psr\Http\Message\ResponseFactoryInterface::class, $args);
		        $this->singleton_get_Psr_Http_Message_ResponseInterface = $factory->createResponse(
		            $args['code'] ?? 200,
		$args['reasonPhrase'] ?? ''
		        );
		    }
		    return $this->singleton_get_Psr_Http_Message_ResponseInterface;
	}


	private function get_Psr_Http_Message_StreamFactoryInterface(array $args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_StreamFactoryInterface === null) {
		        $this->singleton_get_Psr_Http_Message_StreamFactoryInterface = new \Maduser\Argon\Http\Message\Factory\StreamFactory();
		    }
		    return $this->singleton_get_Psr_Http_Message_StreamFactoryInterface;
	}


	private function get_Psr_Http_Message_StreamInterface(array &$args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_StreamInterface === null) {
		        $factory = $this->get(\Psr\Http\Message\StreamFactoryInterface::class, $args);
		        $this->singleton_get_Psr_Http_Message_StreamInterface = $factory->createStream(
		            $args['content'] ?? ''
		        );
		    }
		    return $this->singleton_get_Psr_Http_Message_StreamInterface;
	}


	private function get_Psr_Http_Message_UriFactoryInterface(array $args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_UriFactoryInterface === null) {
		        $this->singleton_get_Psr_Http_Message_UriFactoryInterface = new \Maduser\Argon\Http\Message\Factory\UriFactory();
		    }
		    return $this->singleton_get_Psr_Http_Message_UriFactoryInterface;
	}


	private function get_Psr_Http_Message_UriInterface(array &$args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_UriInterface === null) {
		        $factory = $this->get(\Psr\Http\Message\UriFactoryInterface::class, $args);
		        $this->singleton_get_Psr_Http_Message_UriInterface = $factory->createUri(
		            $args['uri'] ?? ''
		        );
		    }
		    return $this->singleton_get_Psr_Http_Message_UriInterface;
	}


	private function get_Psr_Http_Message_UploadedFileFactoryInterface(array $args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_UploadedFileFactoryInterface === null) {
		        $this->singleton_get_Psr_Http_Message_UploadedFileFactoryInterface = new \Maduser\Argon\Http\Message\Factory\UploadedFileFactory();
		    }
		    return $this->singleton_get_Psr_Http_Message_UploadedFileFactoryInterface;
	}


	private function get_Psr_Http_Message_UploadedFileInterface(array &$args = []): object
	{
		if ($this->singleton_get_Psr_Http_Message_UploadedFileInterface === null) {
		        $factory = $this->get(\Psr\Http\Message\UploadedFileFactoryInterface::class, $args);
		        $this->singleton_get_Psr_Http_Message_UploadedFileInterface = $factory->createUploadedFile(
		            $args['stream'] ?? $this->get('Psr\Http\Message\StreamInterface'),
		$args['size'] ?? NULL,
		$args['error'] ?? 0,
		$args['clientFilename'] ?? NULL,
		$args['clientMediaType'] ?? NULL
		        );
		    }
		    return $this->singleton_get_Psr_Http_Message_UploadedFileInterface;
	}


	private function get_Maduser_Argon_Prophecy_Provider_ArgonRequestHandlerServiceProvider(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonRequestHandlerServiceProvider === null) {
		        $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonRequestHandlerServiceProvider = new \Maduser\Argon\Prophecy\Provider\ArgonRequestHandlerServiceProvider();
		    }
		    return $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonRequestHandlerServiceProvider;
	}


	private function get_Maduser_Argon_Contracts_Http_Server_Factory_RequestHandlerFactoryInterface(
		array $args = [],
	): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_Server_Factory_RequestHandlerFactoryInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Factory_RequestHandlerFactoryInterface = new \Maduser\Argon\Http\Server\Factory\RequestHandlerFactory($args['container'] ?? $this->get('Maduser\Argon\Container\ArgonContainer'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? 'Psr\\Log\\LoggerInterface' ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Factory_RequestHandlerFactoryInterface;
	}


	private function get_Psr_Http_Server_RequestHandlerInterface(array &$args = []): object
	{
		if ($this->singleton_get_Psr_Http_Server_RequestHandlerInterface === null) {
		        $factory = $this->get(\Maduser\Argon\Contracts\Http\Server\Factory\RequestHandlerFactoryInterface::class, $args);
		        $this->singleton_get_Psr_Http_Server_RequestHandlerInterface = $factory->create(

		        );
		    }
		    return $this->singleton_get_Psr_Http_Server_RequestHandlerInterface;
	}


	private function get_Maduser_Argon_Contracts_Http_Server_ResultContextInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_Server_ResultContextInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_Server_ResultContextInterface = new \Maduser\Argon\Http\Server\ResultContext();
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_Server_ResultContextInterface;
	}


	private function get_Maduser_Argon_Prophecy_Provider_ArgonMiddlewareServiceProvider(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMiddlewareServiceProvider === null) {
		        $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMiddlewareServiceProvider = new \Maduser\Argon\Prophecy\Provider\ArgonMiddlewareServiceProvider();
		    }
		    return $this->singleton_get_Maduser_Argon_Prophecy_Provider_ArgonMiddlewareServiceProvider;
	}


	private function get_Maduser_Argon_Contracts_Http_Server_Middleware_DispatcherInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_DispatcherInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_DispatcherInterface = new \Maduser\Argon\Http\Server\Middleware\Dispatcher($args['result'] ?? $this->get('Maduser\Argon\Contracts\Http\Server\ResultContextInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_DispatcherInterface;
	}


	private function get_Maduser_Argon_Contracts_Http_Server_Middleware_JsonResponderInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_JsonResponderInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_JsonResponderInterface = new \Maduser\Argon\Http\Server\Middleware\JsonResponder($args['responseFactory'] ?? $this->get('Psr\Http\Message\ResponseFactoryInterface'),
		$args['streamFactory'] ?? $this->get('Psr\Http\Message\StreamFactoryInterface'),
		$args['result'] ?? $this->get('Maduser\Argon\Contracts\Http\Server\ResultContextInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_JsonResponderInterface;
	}


	private function get_Maduser_Argon_Contracts_Http_Server_Middleware_HtmlResponderInterface(array $args = []): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_HtmlResponderInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_HtmlResponderInterface = new \Maduser\Argon\Http\Server\Middleware\HtmlResponder($args['responseFactory'] ?? $this->get('Psr\Http\Message\ResponseFactoryInterface'),
		$args['streamFactory'] ?? $this->get('Psr\Http\Message\StreamFactoryInterface'),
		$args['result'] ?? $this->get('Maduser\Argon\Contracts\Http\Server\ResultContextInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_HtmlResponderInterface;
	}


	private function get_Maduser_Argon_Contracts_Http_Server_Middleware_PlainTextResponderInterface(
		array $args = [],
	): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_PlainTextResponderInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_PlainTextResponderInterface = new \Maduser\Argon\Http\Server\Middleware\PlainTextResponder($args['responseFactory'] ?? $this->get('Psr\Http\Message\ResponseFactoryInterface'),
		$args['streamFactory'] ?? $this->get('Psr\Http\Message\StreamFactoryInterface'),
		$args['result'] ?? $this->get('Maduser\Argon\Contracts\Http\Server\ResultContextInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_PlainTextResponderInterface;
	}


	private function get_Maduser_Argon_Contracts_Http_Server_Middleware_ResponseResponderInterface(
		array $args = [],
	): object
	{
		if ($this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_ResponseResponderInterface === null) {
		        $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_ResponseResponderInterface = new \Maduser\Argon\Http\Server\Middleware\ResponseResponder($args['result'] ?? $this->get('Maduser\Argon\Contracts\Http\Server\ResultContextInterface'),
		$args['logger'] ?? $this->get('Psr\Log\LoggerInterface') ?? NULL);
		    }
		    return $this->singleton_get_Maduser_Argon_Contracts_Http_Server_Middleware_ResponseResponderInterface;
	}


	public function has(string $id): bool
	{
		return isset($this->serviceMap[$id]) || parent::has($id);
	}


	public function get(string $id, array $args = []): object
	{
		$instance = $this->applyPreInterceptors($id, $args);
		    if ($instance !== null) {
		        return $instance;
		    }

		    $instance = isset($this->serviceMap[$id])
		        ? $this->{$this->serviceMap[$id]}($args)
		        : parent::get($id, $args);

		    return $this->applyPostInterceptors($instance);
	}


	public function getTagged(string $tag): array
	{
		if (!isset($this->tagMap[$tag])) {
		        return [];
		    }

		    $results = [];
		    foreach (array_keys($this->tagMap[$tag]) as $id) {
		        $results[] = $this->get($id);
		    }

		    return $results;
	}


	public function getTaggedIds(string $tag): array
	{
		return array_keys($this->tagMap[$tag] ?? []);
	}


	public function getTaggedMeta(string $tag): array
	{
		return $this->tagMap[$tag] ?? [];
	}


	private function applyPreInterceptors(string $id, array &$args = []): ?object
	{
		foreach ($this->preInterceptors as $interceptor) {
		        if ($interceptor::supports($id)) {
		            $result = (new $interceptor())->intercept($id, $args);
		            if ($result !== null) {
		                return $result;
		            }
		        }
		    }
		    return null;
	}


	private function applyPostInterceptors(object $instance): object
	{
		foreach ($this->postInterceptors as $interceptor) {
		        if ($interceptor::supports($instance)) {
		            (new $interceptor())->intercept($instance);
		        }
		    }
		    return $instance;
	}


	public function invoke(callable|object|array|string $target, array $arguments = []): mixed
	{
		if (is_callable($target) && !is_array($target)) {
		        $reflection = new \ReflectionFunction($target);
		        $instance = null;
		    } elseif (is_array($target) && count($target) === 2) {
		        [$controller, $method] = $target;
		        $instance = is_object($controller) ? $controller : $this->get($controller);
		        $reflection = new \ReflectionMethod($instance, $method);
		    } else {
		        $instance = is_object($target) ? $target : $this->get($target);
		        $reflection = new \ReflectionMethod($instance, '__invoke');
		    }

		    $params = [];

		    foreach ($reflection->getParameters() as $param) {
		        $name = $param->getName();
		        $type = $param->getType()?->getName();

		        if (array_key_exists($name, $arguments)) {
		            $params[] = $arguments[$name];
		        } elseif ($type && $this->has($type)) {
		            $params[] = $this->get($type);
		        } elseif ($param->isDefaultValueAvailable()) {
		            $params[] = $param->getDefaultValue();
		        } else {
		            throw new \RuntimeException("Unable to resolve parameter '{$name}' for '{$reflection->getName()}'");
		        }
		    }

		    return $reflection->invokeArgs($instance, $params);
	}


	private function invokeServiceMethod(string $serviceId, string $method, array $args = []): mixed
	{
		$compiledMethod = $this->buildCompiledInvokerMethodName($serviceId, $method);

		    if (method_exists($this, $compiledMethod)) {
		        return $this->{$compiledMethod}($args);
		    }

		    return $this->invoke([$serviceId, $method], $args);
	}


	private function buildCompiledInvokerMethodName(string $serviceId, string $method = '__invoke'): string
	{
		$sanitizedService = preg_replace('/[^A-Za-z0-9_]/', '_', $serviceId);
		        $sanitizedMethod  = preg_replace('/[^A-Za-z0-9_]/', '_', $method);

		        return 'invoke_' . $sanitizedService . '__' . $sanitizedMethod;
	}
}
