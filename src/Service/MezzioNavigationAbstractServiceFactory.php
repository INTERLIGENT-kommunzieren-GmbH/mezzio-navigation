<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Navigation\Service;

use ArrayAccess;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;

use function array_key_exists;
use function is_array;
use function str_starts_with;
use function strlen;
use function strtolower;
use function substr;

final class MezzioNavigationAbstractServiceFactory extends AbstractMezzioNavigationFactory implements
    AbstractFactoryInterface
{
    /** Top-level configuration key indicating navigation configuration */
    public const string CONFIG_KEY = 'navigation';

    /** Service manager factory prefix */
    public const string SERVICE_PREFIX = 'Laminas\\Navigation\\';

    /**
     * Navigation configuration
     *
     * @var array<array-key, mixed>|null
     */
    private ?array $config = null;

    /** @var array<string, Navigation> */
    private array $containers = [];

    /**
     * Can we create a navigation by the requested name?
     *
     * @param string $requestedName Name by which service was requested, must
     *                              start with Laminas\Navigation\
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        $requestedName = $this->normalizeRequestedName((string) $requestedName);

        if (! str_starts_with($requestedName, self::SERVICE_PREFIX)) {
            return false;
        }

        if (array_key_exists($requestedName, $this->containers)) {
            return true;
        }

        return $this->hasNamedConfig($requestedName, $this->getConfig($container));
    }

    /**
     * {@inheritDoc}
     *
     * @param string $requestedName
     * @param array<array-key, mixed>|null $options
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): Navigation {
        $requestedName = $this->normalizeRequestedName((string) $requestedName);

        // Is already created?
        if (array_key_exists($requestedName, $this->containers)) {
            return $this->containers[$requestedName];
        }

        // Get config
        $config          = $this->getConfig($container);
        $pagesFromConfig = $this->getPagesFromConfig(
            $this->getNamedConfig($requestedName, $config)
        );

        // Prepare pages
        $pages = $this->preparePages(
            $container,
            $pagesFromConfig
        );

        // Create navigation
        return $this->containers[$requestedName] = new Navigation($pages);
    }

    /**
     * Sets the name to "default" if this factory is used for a single navigation
     */
    private function normalizeRequestedName(string $requestedName): string
    {
        if ($requestedName === Navigation::class) {
            return 'Laminas\Navigation\Default';
        }

        return $requestedName;
    }

    /**
     * Get navigation configuration, if any
     *
     * @return array<array-key, mixed>
     */
    private function getConfig(ContainerInterface $container): array
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (! $container->has('config')) {
            return $this->config = [];
        }

        $config = $container->get('config');
        if (
            ! isset($config[self::CONFIG_KEY])
            || ! is_array($config[self::CONFIG_KEY])
        ) {
            return $this->config = [];
        }

        return $this->config = $config[self::CONFIG_KEY];
    }

    /**
     * Extract config name from service name
     */
    private function getConfigName(string $name): string
    {
        return substr($name, strlen(self::SERVICE_PREFIX));
    }

    /**
     * Does the configuration have a matching named section?
     *
     * @param array<array-key, mixed>|ArrayAccess<array-key, mixed> $config
     */
    private function hasNamedConfig(string $name, array|ArrayAccess $config): bool
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return true;
        }

        return isset($config[strtolower($withoutPrefix)]);
    }

    /**
     * Get the matching named configuration section.
     *
     * @param array<array-key, mixed>|ArrayAccess<array-key, mixed> $config
     * @return array<array-key, mixed>
     */
    private function getNamedConfig(string $name, array|ArrayAccess $config): array
    {
        $withoutPrefix = $this->getConfigName($name);

        if (isset($config[$withoutPrefix])) {
            return $config[$withoutPrefix];
        }

        if (isset($config[strtolower($withoutPrefix)])) {
            return $config[strtolower($withoutPrefix)];
        }

        return [];
    }
}
