<?php

declare(strict_types=1);

namespace Maduser\Argon\Middleware\Loader;

use Maduser\Argon\Middleware\Contracts\MiddlewareLoaderInterface;
use Maduser\Argon\Middleware\Exception\MiddlewareException;
use Maduser\Argon\Container\ArgonContainer;
use Maduser\Argon\Middleware\MiddlewareDefinition;

final readonly class TaggedMiddlewareLoader implements MiddlewareLoaderInterface
{
    public function __construct(
        private ArgonContainer $container,
        private string $tag
    ) {
    }

    /**
     * @return list<MiddlewareDefinition>
     */
    public function load(): array
    {
        $tagged = $this->container->getTaggedMeta($this->tag);

        $definitions = [];
        foreach ($tagged as $class => $meta) {
            $priority = (int) $meta['priority'];
            $definitions[] = new MiddlewareDefinition($class, $priority);
        }

        return $definitions;
    }

    /**
     * @return array<string, list<MiddlewareDefinition>>
     */
    public function loadGrouped(): array
    {
        $tagged = $this->container->getTaggedMeta($this->tag);
        $groups = [];

        foreach ($tagged as $class => $meta) {
            $priority = (int) $meta['priority'];
            $group = (string) ($meta['group'] ?? MiddlewareDefinition::DEFAULT_GROUP);
            $definition = new MiddlewareDefinition($class, $priority);

            $groups[$group][] = $definition;
        }

        return $groups;
    }
}
