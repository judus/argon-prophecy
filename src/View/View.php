<?php

declare(strict_types=1);

namespace Maduser\Argon\View;

use InvalidArgumentException;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\Http\Server\Middleware\HtmlableInterface;
use Maduser\Argon\View\Contracts\TemplateEngineInterface;
use Maduser\Argon\View\Response\ViewResponse;
use RuntimeException;

final class View
{
    /** @var array<string, class-string<TemplateEngineInterface>> */
    private array $engineMap = [];

    /** @var array<class-string<TemplateEngineInterface>, TemplateEngineInterface> */
    private array $resolved = [];

    public function __construct(private readonly ArgonContainer $container) {}

    public function getEngineMap(): array
    {
        return $this->engineMap;
    }

    public function registerEngine(string $extension, string $class): self
    {
        if (!is_subclass_of($class, TemplateEngineInterface::class)) {
            throw new InvalidArgumentException("Class $class must implement TemplateEngineInterface");
        }

        $this->engineMap[strtolower($extension)] = $class;
        return $this;
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function render(string $template, array $data = []): HtmlableInterface
    {
        return new ViewResponse(
            engine: $this->resolveEngine($template),
            template: $template,
            data: $data
        );
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    public function renderToString(string $template, array $data = []): string
    {
        return $this->resolveEngine($template)->render($template, $data);
    }

    /**
     * @throws ContainerException
     * @throws NotFoundException
     */
    private function resolveEngine(string $template): TemplateEngineInterface
    {
        $ext = strtolower(pathinfo($template, PATHINFO_EXTENSION));

        if (!isset($this->engineMap[$ext])) {
            throw new RuntimeException("No template engine registered for extension: .$ext");
        }

        $class = $this->engineMap[$ext];

        if (!isset($this->resolved[$class])) {
            $this->resolved[$class] = $this->container->get($class);
        }

        return $this->resolved[$class];
    }
}
