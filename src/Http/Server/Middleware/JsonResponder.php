<?php

declare(strict_types=1);

namespace Maduser\Argon\Http\Server\Middleware;

use JsonException;
use JsonSerializable;
use Maduser\Argon\Contracts\Http\Server\Middleware\JsonResponderInterface;
use Maduser\Argon\Contracts\Http\Server\ResultContextInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class JsonResponder extends AbstractResponder implements JsonResponderInterface
{
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        private ResultContextInterface $result,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($responseFactory, $streamFactory, $logger);
    }

    /**
     * @throws JsonException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var array|JsonSerializable $raw */
        $raw = $this->result->get();

        if ($this->result->isArray() || $raw instanceof JsonSerializable) {
            /** @var array|string|null $data */
            $data = $raw instanceof JsonSerializable
                ? $raw->jsonSerialize()
                : $raw;

            $json = json_encode($data, JSON_THROW_ON_ERROR);

            return $this->createResponse($json, 'application/json; charset=UTF-8');
        }

        return $handler->handle($request);
    }
}
