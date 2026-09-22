<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Navigation\Service;

use Laminas\Navigation\Navigation;
use Mezzio\Helper\UrlHelper;
use Mezzio\Navigation\Service\MezzioNavigationAbstractServiceFactory;
use Mezzio\Router\LaminasRouter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

#[CoversClass(MezzioNavigationAbstractServiceFactory::class)]
final class MezzioNavigationAbstractServiceFactoryTest extends TestCase
{
    private MezzioNavigationAbstractServiceFactory $factory;

    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->factory   = new MezzioNavigationAbstractServiceFactory();
        $this->container = $this->createContainer([
            'navigation' => [
                'default' => [
                    ['route' => 'home'],
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createContainer(array $config): ContainerInterface
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')
            ->willReturnCallback(static fn (string $id): bool => $id === 'config');
        $container->method('get')
            ->willReturnCallback(static fn (string $id): mixed => match ($id) {
                'config'          => $config,
                UrlHelper::class  => new UrlHelper(new LaminasRouter()),
                default           => null,
            });

        return $container;
    }

    public function testInvokeMethodShouldReturnNavigationInstance(): void
    {
        self::assertInstanceOf(
            Navigation::class,
            ($this->factory)($this->container, Navigation::class)
        );
    }

    public function testInvokeMethodShouldReturnTheSameInstanceOnSecondCall(): void
    {
        $first  = ($this->factory)($this->container, Navigation::class);
        $second = ($this->factory)($this->container, Navigation::class);

        self::assertSame($first, $second);
    }

    public function testCanCreateMethodWithValidName(): void
    {
        self::assertTrue(
            $this->factory->canCreate($this->container, Navigation::class)
        );
    }

    public function testCanCreateMethodWithInvalidName(): void
    {
        self::assertFalse(
            $this->factory->canCreate($this->container, 'Foobar')
        );
    }

    public function testCreationWithEmptyConfigShouldReturnEmptyNavigation(): void
    {
        $result = ($this->factory)($this->createContainer([]), Navigation::class);

        self::assertCount(0, $result);
    }
}
