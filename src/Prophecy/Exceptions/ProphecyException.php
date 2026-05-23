<?php

declare(strict_types=1);

namespace Maduser\Argon\Prophecy\Exceptions;

use RuntimeException;
use Throwable;

final class ProphecyException extends RuntimeException
{
    public static function applicationNotBooted(): self
    {
        return new self('Application not booted yet.');
    }

    public static function applicationAlreadyBooted(): self
    {
        return new self('Application already booted.');
    }

    public static function applicationHasBeenReset(): self
    {
        return new self('Application has been reset and cannot be used again.');
    }

    public static function invalidCompileFlag(string $value): self
    {
        return new self(sprintf(
            'Invalid container compilation flag value "%s". Expected one of: true, false, 1, 0, yes, no, on, off.',
            $value
        ));
    }

    /**
     * @param list<string> $missing
     */
    public static function incompleteCompileConfiguration(array $missing): self
    {
        return new self(sprintf(
            'Container compilation is enabled but compile configuration is incomplete. Missing: %s.',
            implode(', ', $missing)
        ));
    }

    public static function noApplicationHandlerRegistered(): self
    {
        return new self('No application handler registered.');
    }

    /**
     * @param list<class-string> $handlerIds
     */
    public static function multipleApplicationHandlersRegistered(array $handlerIds): self
    {
        return new self(sprintf(
            'Multiple application handlers registered (%s). Bind %s to choose the active handler.',
            implode(', ', $handlerIds),
            \Maduser\Argon\Contracts\Handler\AppHandlerInterface::class
        ));
    }

    public static function invalidApplicationHandlerBinding(string $id, object $handler): self
    {
        return new self(sprintf(
            'Application handler binding %s must resolve to %s; got %s.',
            $id,
            \Maduser\Argon\Contracts\Handler\AppHandlerInterface::class,
            $handler::class
        ));
    }

    public static function runtimeErrorHandlerResolutionFailed(Throwable $previous): self
    {
        return new self(
            'Runtime error handler is registered but could not be resolved.',
            0,
            $previous
        );
    }

    public static function invalidRuntimeErrorHandlerBinding(object $handler): self
    {
        return new self(sprintf(
            'Runtime error handler binding %s must resolve to %s; got %s.',
            \Maduser\Argon\Support\Contracts\ErrorHandlerInterface::class,
            \Maduser\Argon\Support\Contracts\ErrorHandlerInterface::class,
            $handler::class
        ));
    }

    public static function runtimeErrorHandlerRegistrationFailed(Throwable $previous): self
    {
        return new self(
            'Runtime error handler is registered but failed during registration.',
            0,
            $previous
        );
    }

    public static function unsupportedHttpProcessing(): self
    {
        return new self('Active handler does not support HTTP processing.');
    }

    public static function unsupportedHttpEmission(): self
    {
        return new self('Active handler does not support HTTP emission.');
    }

    public static function unableToDetermineWorkingDirectory(): self
    {
        return new self('Unable to determine working directory from current environment.');
    }

    public static function scriptFilenameMissing(): self
    {
        return new self('Unable to determine working directory; SCRIPT_FILENAME is not defined.');
    }

    public static function compiledContainerClassNotFound(string $className): self
    {
        return new self("Compiled container class '{$className}' not found.");
    }

    public static function compiledContainerMustExtendArgonContainer(): self
    {
        return new self('Compiled container must extend ArgonContainer.');
    }

    public static function cwdMutationAttempted(): self
    {
        return new self('Service configuration attempted to mutate cwd parameter.');
    }

    public static function cwdMissing(): self
    {
        return new self('Current working directory must be provided to ContainerManager.');
    }
}
