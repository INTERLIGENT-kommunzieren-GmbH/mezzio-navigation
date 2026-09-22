<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Navigation\Service;

use Laminas\Navigation\Exception\InvalidArgumentException;
use Laminas\Navigation\Navigation;
use Mezzio\Helper\UrlHelper;
use Mezzio\Navigation\Service\MezzioNavigationFactory;
use Mezzio\Router\LaminasRouter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionClass;

#[CoversClass(MezzioNavigationFactory::class)]
final class MezzioNavigationFactoryTest extends TestCase
{
    private MezzioNavigationFactory $factory;

    private ContainerInterface $container;

    protected function setUp(): void
    {
        $this->factory   = new MezzioNavigationFactory();
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
            ($this->factory)($this->container)
        );
    }

    public function testGetPagesSetsPagesProperty(): void
    {
        $property = (new ReflectionClass($this->factory))->getProperty('pages');

        self::assertNull($property->getValue($this->factory));

        ($this->factory)($this->container);

        self::assertIsArray($property->getValue($this->factory));

        // Second invocation must reuse the cached pages
        ($this->factory)($this->container);

        self::assertIsArray($property->getValue($this->factory));
    }

    public function testMissingNavigationConfigShouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ($this->factory)($this->createContainer([]));
    }

    public function testMissingDefaultConfigShouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ($this->factory)($this->createContainer(['navigation' => []]));
    }
}
