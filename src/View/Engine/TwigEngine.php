<?php

declare(strict_types=1);

namespace Maduser\Argon\View\Engine;

use Maduser\Argon\View\Contracts\TemplateEngineInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

final class TwigEngine implements TemplateEngineInterface
{
    private FilesystemLoader $loader;
    private Environment $twig;

    public function __construct(string $viewsPath)
    {
        $this->loader = new FilesystemLoader($viewsPath);
        $this->twig = new Environment($this->loader);
    }

    /**
     * @throws LoaderError
     */
    public function addPath(string $path, string $namespace): void
    {
        $this->loader->addPath(realpath($path), $namespace);
    }

    public function getEnvironment(): Environment
    {
        return $this->twig;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(string $template, array $context = []): string
    {
        return $this->twig->render($template, $context);
    }
}