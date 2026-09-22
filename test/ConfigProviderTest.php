<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace IkossTest\Mezzio\Navigation;

use Ikoss\Mezzio\Navigation\ConfigProvider;
use Ikoss\Mezzio\Navigation\Middleware;
use Ikoss\Mezzio\Navigation\Service;
use Laminas\Navigation\Navigation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigProvider::class)]
final class ConfigProviderTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $config = [
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

    public function testProvidesExpectedConfiguration(): ConfigProvider
    {
        $provider = new ConfigProvider();
        self::assertSame($this->config, $provider->getDependencyConfig());

        return $provider;
    }

    #[Depends('testProvidesExpectedConfiguration')]
    public function testInvocationProvidesDependencyConfiguration(
        ConfigProvider $provider
    ): void {
        self::assertSame(
            ['dependencies' => $provider->getDependencyConfig()],
            $provider()
        );
    }
}
