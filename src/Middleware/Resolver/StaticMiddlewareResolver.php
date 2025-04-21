<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Resolver;

use Maduser\Argon\Middleware\Contracts\MiddlewareResolverInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Psr\Http\Server\MiddlewareInterface;

final class StaticMiddlewareResolver implements MiddlewareResolverInterface
{
    /** @var array<class-string<MiddlewareInterface>, MiddlewareInterface> */
    private array $instances = [];

    /** @param array<class-string<MiddlewareInterface>, MiddlewareInterface> $instances */
    public function __construct(array $instances)
    {
        foreach ($instances as $class => $instance) {
            if (!$instance instanceof MiddlewareInterface) {
                throw new MiddlewareException("Instance for '$class' must implement MiddlewareInterface.");
            }
            $this->instances[$class] = $instance;
        }
    }

    public function resolve(string $class): MiddlewareInterface
    {
        if (!isset($this->instances[$class])) {
            throw new MiddlewareException("No middleware registered for class '$class'.");
        }

        return $this->instances[$class];
    }
}
