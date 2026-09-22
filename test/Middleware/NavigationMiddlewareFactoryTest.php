<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace IkossTest\Mezzio\Navigation\Middleware;

use Ikoss\Mezzio\Navigation\Middleware\NavigationMiddleware;
use Ikoss\Mezzio\Navigation\Middleware\NavigationMiddlewareFactory;
use Ikoss\Mezzio\Navigation\Page\MezzioPage;
use Laminas\Navigation\Navigation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionObject;

use function array_shift;

#[CoversClass(NavigationMiddlewareFactory::class)]
final class NavigationMiddlewareFactoryTest extends TestCase
{
    private NavigationMiddlewareFactory $factory;

    private Navigation $navigation;

    protected function setUp(): void
    {
        $this->factory    = new NavigationMiddlewareFactory();
        $this->navigation = new Navigation();
    }

    /**
     * @param array<string, mixed> $services
     */
    private function createContainer(bool $hasConfig, array $services = []): ContainerInterface
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(static fn (string $id): bool => $id === 'config' && $hasConfig);
        $container->method('get')
            ->willReturnCallback(static fn (string $id): mixed => $services[$id] ?? null);

        return $container;
    }

    public function testFactoryWithMultipleNavigations(): void
    {
        $container = $this->createContainer(true, [
            'config'                     => [
                'navigation' => [
                    'default' => [],
                    'special' => [],
                ],
            ],
            'Laminas\Navigation\Default' => $this->navigation,
            'Laminas\Navigation\Special' => $this->navigation,
        ]);

        self::assertInstanceOf(
            NavigationMiddleware::class,
            ($this->factory)($container)
        );
    }

    public function testFactoryWithOneNavigation(): void
    {
        $container = $this->createContainer(true, [
            'config'          => ['navigation' => ['default' => []]],
            Navigation::class => $this->navigation,
        ]);

        self::assertInstanceOf(
            NavigationMiddleware::class,
            ($this->factory)($container)
        );
    }

    public function testFactoryWithOneNavigationAndCustomNavigationName(): void
    {
        $this->navigation->addPage(new MezzioPage(['route' => 'home']));

        $container = $this->createContainer(true, [
            'config'                     => ['navigation' => ['special' => []]],
            'Laminas\Navigation\Special' => $this->navigation,
        ]);

        $middleware = ($this->factory)($container);
        self::assertInstanceOf(NavigationMiddleware::class, $middleware);

        $property   = (new ReflectionObject($middleware))->getProperty('containers');
        $containers = $property->getValue($middleware);

        self::assertSame($this->navigation, array_shift($containers));
    }

    public function testFactoryWithoutConfigShouldReturnMiddleware(): void
    {
        self::assertInstanceOf(
            NavigationMiddleware::class,
            ($this->factory)($this->createContainer(false))
        );
    }

    public function testFactoryWithoutNavigationConfigShouldReturnMiddleware(): void
    {
        $container = $this->createContainer(true, ['config' => []]);

        self::assertInstanceOf(
            NavigationMiddleware::class,
            ($this->factory)($container)
        );
    }
}
