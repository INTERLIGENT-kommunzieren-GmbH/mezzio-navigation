<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace IkossTest\Mezzio\Navigation\Page;

use Ikoss\Mezzio\Navigation\Page\MezzioPage;
use Laminas\Diactoros\ServerRequest;
use Laminas\Navigation\Exception\DomainException;
use Laminas\Navigation\Exception\InvalidArgumentException;
use Mezzio\Helper\Exception\RuntimeException as UrlHelperRuntimeException;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\LaminasRouter;
use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;

#[CoversClass(MezzioPage::class)]
final class MezzioPageTest extends TestCase
{
    private Route $route;

    private RouteResult $routeResult;

    private UrlHelper $urlHelper;

    protected function setUp(): void
    {
        $middleware = $this->createStub(MiddlewareInterface::class);

        $this->route = new Route('/foo', $middleware, ['GET'], 'foo');

        $router = new LaminasRouter();
        $router->addRoute($this->route);

        $request = new ServerRequest(
            ['REQUEST_METHOD' => 'GET'],
            [],
            '/foo',
            'GET'
        );

        $this->routeResult = $router->match($request);

        $this->urlHelper = new UrlHelper($router);
        // UrlHelperMiddleware does this in a real pipeline; setRouteResult()
        // requires a request to have been injected first.
        $this->urlHelper->setRequest($request);
    }

    public function testGetHref(): void
    {
        $page = new MezzioPage([
            'route'        => 'foo',
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
        ]);

        self::assertSame('/foo', $page->getHref());
    }

    public function testGetHrefWithoutRouteName(): void
    {
        $page = new MezzioPage([
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
        ]);

        self::assertSame('/foo', $page->getHref());
    }

    public function testGetHrefWithFragment(): void
    {
        $page = new MezzioPage([
            'route'        => 'foo',
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
            'fragment'     => 'bar',
        ]);

        self::assertSame('/foo#bar', $page->getHref());
    }

    public function testGetHrefWithQueryParams(): void
    {
        $page = new MezzioPage([
            'route'        => 'foo',
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
            'query'        => [
                'bar' => 1,
                'baz' => 2,
            ],
        ]);

        self::assertSame('/foo?bar=1&baz=2', $page->getHref());
    }

    public function testGetHrefWithBasePath(): void
    {
        $page = new MezzioPage([
            'route'        => 'foo',
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
        ]);

        $this->urlHelper->setBasePath('bar');

        self::assertSame('/bar/foo', $page->getHref());
    }

    public function testGetHrefWithFailedResultSet(): void
    {
        $page = new MezzioPage([
            'url_helper'   => $this->urlHelper,
            'route_result' => RouteResult::fromRouteFailure(null),
        ]);

        $this->expectException(UrlHelperRuntimeException::class);

        $page->getHref();
    }

    public function testGetHrefWithoutUrlHelperShouldThrowException(): void
    {
        $page = new MezzioPage(['route' => 'foo']);

        $this->expectException(DomainException::class);

        $page->getHref();
    }

    public function testGetHrefWithRouteResultOnUrlHelperAndNotPageShouldGenerateHref(): void
    {
        $this->urlHelper->setRouteResult($this->routeResult);

        $page = new MezzioPage(['url_helper' => $this->urlHelper]);

        self::assertSame('/foo', $page->getHref());
    }

    public function testGetHrefSetsHrefCache(): void
    {
        $page = new MezzioPage([
            'route'        => 'foo',
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
        ]);

        $property = (new ReflectionClass($page))->getProperty('hrefCache');

        self::assertNull($property->getValue($page));

        self::assertSame('/foo', $page->getHref());
        self::assertSame('/foo', $property->getValue($page));

        // Second call must come from the cache
        self::assertSame('/foo', $page->getHref());
    }

    public function testIsActive(): void
    {
        $page = new MezzioPage([
            'route'        => 'foo',
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
        ]);

        self::assertTrue($page->isActive());
    }

    public function testIsActiveWithoutRoute(): void
    {
        $page = new MezzioPage([
            'url_helper'   => $this->urlHelper,
            'route_result' => $this->routeResult,
        ]);

        self::assertFalse($page->isActive());
    }

    public function testSetRoutePerConstructor(): void
    {
        $page = new MezzioPage(['route' => 'foo']);

        self::assertSame('foo', $page->getRoute());
    }

    public function testSetRoutePerMethod(): void
    {
        $page = new MezzioPage();
        $page->setRoute('foo');

        self::assertSame('foo', $page->getRoute());
    }

    public function testSetRouteToNull(): void
    {
        $page = new MezzioPage();
        $page->setRoute(null);

        self::assertNull($page->getRoute());
    }

    public function testInvalidArgumentForRouteShouldThrowException(): void
    {
        $page = new MezzioPage();

        $this->expectException(InvalidArgumentException::class);

        $page->setRoute('');
    }

    public function testSetRouterPerConstructor(): void
    {
        $page = new MezzioPage(['url_helper' => $this->urlHelper]);

        self::assertSame($this->urlHelper, $page->getUrlHelper());
    }

    public function testSetUrlPerMethod(): void
    {
        $page = new MezzioPage();
        $page->setUrlHelper($this->urlHelper);

        self::assertSame($this->urlHelper, $page->getUrlHelper());
    }

    public function testSetRouteResultPerConstructor(): void
    {
        $page = new MezzioPage(['route_result' => $this->routeResult]);

        self::assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetRouteResultPerMethod(): void
    {
        $page = new MezzioPage();
        $page->setRouteResult($this->routeResult);

        self::assertSame($this->routeResult, $page->getRouteResult());
    }

    public function testSetParamsPerConstructor(): void
    {
        $params = ['foo' => 'bar'];
        $page   = new MezzioPage(['params' => $params]);

        self::assertSame($params, $page->getParams());
    }

    public function testSetParamsPerMethod(): void
    {
        $params = ['foo' => 'bar'];
        $page   = new MezzioPage();
        $page->setParams($params);

        self::assertSame($params, $page->getParams());
    }

    public function testSetQueryPerConstructor(): void
    {
        $query = ['foo' => 'bar'];
        $page  = new MezzioPage(['query' => $query]);

        self::assertSame($query, $page->getQuery());
    }

    public function testSetQueryPerMethod(): void
    {
        $query = ['foo' => 'bar'];
        $page  = new MezzioPage();
        $page->setQuery($query);

        self::assertSame($query, $page->getQuery());
    }
}
