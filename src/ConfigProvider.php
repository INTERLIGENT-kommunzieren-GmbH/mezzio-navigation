<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace Ikoss\Mezzio\Navigation;

use Laminas\Navigation\Navigation;

class ConfigProvider
{
    /**
     * Return general-purpose mezzio-navigation configuration.
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return application-level dependency configuration.
     *
     * @return array<string, mixed>
     */
    public function getDependencyConfig(): array
    {
        return [
            'abstract_factories' => [
                Service\MezzioNavigationAbstractServiceFactory::class,
            ],
            'aliases'            => [
                'navigation' => Navigation::class,
            ],
            'factories'          => [
                Middleware\NavigationMiddleware::class => Middleware\NavigationMiddlewareFactory::class,
                Navigation::class                      => Service\MezzioNavigationFactory::class,
            ],
        ];
    }
}
