<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace Mezzio\Navigation\Service;

use Laminas\Navigation\Exception;
use Laminas\Navigation\Navigation;
use Psr\Container\ContainerInterface;

class MezzioNavigationFactory extends AbstractMezzioNavigationFactory
{
    /** @var array<array-key, mixed>|null */
    private ?array $pages = null;

    /**
     * Create and return a new Navigation instance
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container): Navigation
    {
        return new Navigation($this->getPages($container));
    }

    /**
     * @return array<array-key, mixed>
     * @throws Exception\InvalidArgumentException
     */
    private function getPages(ContainerInterface $container): array
    {
        // Is already created?
        if ($this->pages !== null) {
            return $this->pages;
        }

        $configuration = $container->get('config');

        if (! isset($configuration['navigation'])) {
            throw new Exception\InvalidArgumentException(
                'Could not find navigation configuration key'
            );
        }

        if (! isset($configuration['navigation']['default'])) {
            throw new Exception\InvalidArgumentException(
                'Failed to find a navigation container by the name "default"'
            );
        }

        $pages = $this->getPagesFromConfig(
            $configuration['navigation']['default']
        );

        return $this->pages = $this->preparePages($container, $pages);
    }
}
