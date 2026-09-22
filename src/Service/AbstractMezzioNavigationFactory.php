<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace Ikoss\Mezzio\Navigation\Service;

use Ikoss\Mezzio\Navigation\Page\MezzioPage;
use Laminas\Config;
use Laminas\Navigation\Exception;
use Laminas\Stdlib\ArrayUtils;
use Mezzio\Helper\UrlHelper;
use Psr\Container\ContainerInterface;
use Traversable;

use function file_exists;
use function is_array;
use function is_string;
use function sprintf;

abstract class AbstractMezzioNavigationFactory
{
    /**
     * @param array<array-key, mixed> $pages
     * @return array<array-key, mixed>
     */
    protected function preparePages(
        ContainerInterface $container,
        array $pages
    ): array {
        // Get URL helper
        $urlHelper = $container->get(UrlHelper::class);

        return $this->injectComponents($pages, $urlHelper);
    }

    /**
     * @param array<array-key, mixed> $pages
     * @return array<array-key, mixed>
     */
    protected function injectComponents(
        array $pages,
        ?UrlHelper $urlHelper = null
    ): array {
        foreach ($pages as &$page) {
            if (isset($page['route'])) {
                // Set Mezzio page as page type
                $page['type'] = MezzioPage::class;

                // Set URL helper if exists
                if ($urlHelper !== null && ! isset($page['url_helper'])) {
                    $page['url_helper'] = $urlHelper;
                }
            }

            if (! isset($page['pages'])) {
                continue;
            }

            $page['pages'] = $this->injectComponents(
                $page['pages'],
                $urlHelper
            );
        }

        return $pages;
    }

    /**
     * @param mixed $config String filename, Traversable or array of pages
     * @return array<array-key, mixed>
     * @throws Exception\InvalidArgumentException
     */
    protected function getPagesFromConfig(mixed $config = null): array
    {
        if (is_string($config)) {
            if (! file_exists($config)) {
                throw new Exception\InvalidArgumentException(
                    sprintf(
                        'Config was a string but file "%s" does not exist',
                        $config
                    )
                );
            }

            $config = Config\Factory::fromFile($config);
        } elseif ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }

        if (! is_array($config)) {
            throw new Exception\InvalidArgumentException(
                'Invalid input, expected array, filename, or Traversable object'
            );
        }

        return $config;
    }
}
