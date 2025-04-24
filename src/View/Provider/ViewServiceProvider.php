<?php

declare(strict_types=1);

namespace Maduser\Argon\View\Provider;

use Maduser\Argon\Container\AbstractServiceProvider;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Container\Exceptions\ContainerException;
use Maduser\Argon\Container\Exceptions\NotFoundException;
use Maduser\Argon\Contracts\Http\Server\Middleware\HtmlResponderInterface;
use Maduser\Argon\View\Contracts\TemplateEngineInterface;
use Maduser\Argon\View\Engine\TwigEngine;
use Maduser\Argon\View\View;
use Maduser\Argon\Http\Server\Middleware\HtmlResponder;
use Twig\Error\LoaderError;

class ViewServiceProvider extends AbstractServiceProvider
{
    /**
     * @throws ContainerException
     */
    public function register(ArgonContainer $container): void
    {
        $parameters = $container->getParameters();
        $basePath = (string) $parameters->get('basePath');

        // Register template engines with constructor args
        $container->set(TwigEngine::class, TwigEngine::class, [
            'viewsPath' => $basePath . '/resources/views',
        ])->tag(['view']);

        // Register the View manager
        $container->set(View::class);
    }

    /**
     * @throws ContainerException
     * @throws LoaderError
     * @throws NotFoundException
     */
    public function boot(ArgonContainer $container): void
    {
        $view = $container->get(View::class);

        // Register twig engine for *.twig files
        $view->registerEngine('twig', TwigEngine::class);

        // Add custom path alias (optional)
        $engine = $container->get(TwigEngine::class);
        $engine->addPath(__DIR__ . '/../resources/views', 'custom');
    }
}
