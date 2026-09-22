<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace Ikoss\Mezzio\Navigation\Middleware;

use Ikoss\Mezzio\Navigation\Page\MezzioPage;
use Laminas\Navigation\AbstractContainer;
use Laminas\Navigation\Exception;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RecursiveIteratorIterator;

use function sprintf;

/**
 * Pipeline middleware for injecting Navigations with a RouteResult.
 */
class NavigationMiddleware implements MiddlewareInterface
{
    /** @var list<AbstractContainer> */
    private array $containers = [];

    /**
     * @param array<mixed> $containers
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(array $containers)
    {
        foreach ($containers as $container) {
            if (! $container instanceof AbstractContainer) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Invalid argument: container must be an instance of %s',
                    AbstractContainer::class
                ));
            }

            $this->containers[] = $container;
        }
    }

    /**
     * @inheritDoc
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $routeResult = $request->getAttribute(RouteResult::class, false);

        if (! $routeResult instanceof RouteResult) {
            return $handler->handle($request);
        }

        foreach ($this->containers as $container) {
            $iterator = new RecursiveIteratorIterator(
                $container,
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $page) {
                if (! $page instanceof MezzioPage) {
                    continue;
                }

                $page->setRouteResult($routeResult);
            }
        }

        return $handler->handle($request);
    }
}
