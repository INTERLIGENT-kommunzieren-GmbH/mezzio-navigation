<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace MezzioTest\Navigation\Middleware;

use Laminas\Navigation\Exception\InvalidArgumentException;
use Laminas\Navigation\Navigation;
use Mezzio\Navigation\Middleware\NavigationMiddleware;
use Mezzio\Navigation\Page\MezzioPage;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(NavigationMiddleware::class)]
final class NavigationMiddlewareTest extends TestCase
{
    private NavigationMiddleware $middleware;

    private Navigation $navigation;

    protected function setUp(): void
    {
        $this->navigation = new Navigation([
            new MezzioPage(),
            new MezzioPage(),
            new MezzioPage(),
        ]);

        $this->middleware = new NavigationMiddleware([$this->navigation]);
    }

    public function testRouteResultShouldAddedToPages(): void
    {
        $routeResult = RouteResult::fromRoute(new Route(
            '/foo',
            $this->createStub(MiddlewareInterface::class),
            ['GET'],
            'foo'
        ));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn($routeResult);

        $response = $this->createStub(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $this->middleware->process($request, $handler));

        foreach ($this->navigation as $page) {
            self::assertInstanceOf(MezzioPage::class, $page);
            self::assertSame($routeResult, $page->getRouteResult());
        }
    }

    public function testRequestWithoutRouteResultIsPassedThrough(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with(RouteResult::class, false)
            ->willReturn(false);

        $response = $this->createStub(ResponseInterface::class);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $this->middleware->process($request, $handler));

        foreach ($this->navigation as $page) {
            self::assertInstanceOf(MezzioPage::class, $page);
            self::assertNull($page->getRouteResult());
        }
    }

    public function testInvalidContainerShouldThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new NavigationMiddleware([1]);
    }
}
