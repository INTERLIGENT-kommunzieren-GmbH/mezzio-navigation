<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace Ikoss\Mezzio\Navigation\Middleware;

use Laminas\Navigation\Navigation;
use Psr\Container\ContainerInterface;

use function array_keys;
use function count;
use function current;
use function is_array;
use function ucfirst;

class NavigationMiddlewareFactory
{
    /** Top-level configuration key indicating navigation configuration */
    public const string CONFIG_KEY = 'navigation';

    /** Service manager factory prefix */
    public const string SERVICE_PREFIX = 'Laminas\\Navigation\\';

    /** @var list<string>|null */
    private ?array $containerNames = null;

    public function __invoke(ContainerInterface $container): NavigationMiddleware
    {
        $containers = [];
        foreach ($this->getContainerNames($container) as $containerName) {
            $containers[] = $container->get($containerName);
        }

        return new NavigationMiddleware($containers);
    }

    /**
     * Get navigation container names
     *
     * @return list<string>
     */
    private function getContainerNames(ContainerInterface $container): array
    {
        if ($this->containerNames !== null) {
            return $this->containerNames;
        }

        if (! $container->has('config')) {
            return $this->containerNames = [];
        }

        $config = $container->get('config');
        if (
            ! isset($config[self::CONFIG_KEY])
            || ! is_array($config[self::CONFIG_KEY])
        ) {
            return $this->containerNames = [];
        }

        $names = array_keys($config[self::CONFIG_KEY]);

        if (count($names) === 1 && current($names) === 'default') {
            return $this->containerNames = [Navigation::class];
        }

        $containerNames = [];
        foreach ($names as $name) {
            $containerNames[] = self::SERVICE_PREFIX . ucfirst((string) $name);
        }

        return $this->containerNames = $containerNames;
    }
}
