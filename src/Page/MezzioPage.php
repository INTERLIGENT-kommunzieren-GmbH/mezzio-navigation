<?php

/**
 * @see       https://github.com/INTERLIGENT-kommunzieren-GmbH/mezzio-navigation for the canonical source repository
 */

declare(strict_types=1);

namespace Ikoss\Mezzio\Navigation\Page;

use Laminas\Navigation\Exception;
use Laminas\Navigation\Page\AbstractPage;
use Mezzio\Helper\UrlHelper;
use Mezzio\Router\RouteResult;

use function array_intersect_assoc;
use function count;

class MezzioPage extends AbstractPage
{
    /** Route name */
    private ?string $routeName = null;

    /** @var array<string, mixed> Route parameters */
    private array $routeParams = [];

    /** @var array<string, mixed> Query parameters */
    private array $queryParams = [];

    private ?RouteResult $routeResult = null;

    private ?UrlHelper $urlHelper = null;

    private ?string $hrefCache = null;

    /**
     * @inheritDoc
     */
    public function isActive($recursive = false): bool
    {
        if (
            $this->active
            || $this->routeName === null
            || ! $this->routeResult instanceof RouteResult
        ) {
            return parent::isActive($recursive);
        }

        $intersectionOfParams = array_intersect_assoc(
            $this->routeResult->getMatchedParams(),
            $this->routeParams
        );

        $matchedRouteName = $this->routeResult->getMatchedRouteName();

        if (
            $matchedRouteName === $this->routeName
            && count($intersectionOfParams) === count($this->routeParams)
        ) {
            $this->active = true;

            return $this->active;
        }

        return parent::isActive($recursive);
    }

    /**
     * @inheritDoc
     */
    public function getHref(): string
    {
        // Use cache?
        if ($this->hrefCache !== null) {
            return $this->hrefCache;
        }

        if ($this->urlHelper === null) {
            throw new Exception\DomainException(
                'Ikoss\Mezzio\Navigation\Page\MezzioPage::getHref cannot execute'
                . ' without a Mezzio\Helper\UrlHelper being set'
            );
        }

        if ($this->routeResult instanceof RouteResult) {
            // Set route result
            $this->urlHelper->setRouteResult($this->routeResult);
        }

        // Generate URL
        return $this->hrefCache = $this->urlHelper->generate(
            $this->routeName,
            $this->routeParams,
            $this->queryParams,
            $this->fragment
        );
    }

    /**
     * @throws Exception\InvalidArgumentException
     */
    public function setRoute(?string $route): void
    {
        if ($route === '') {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $route must be a non-empty string or null'
            );
        }

        $this->routeName = $route;
        $this->hrefCache = null;
    }

    public function getRoute(): ?string
    {
        return $this->routeName;
    }

    /**
     * @param array<string, mixed>|null $params
     */
    public function setParams(?array $params = null): void
    {
        $this->routeParams = $params ?: [];
        $this->hrefCache   = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->routeParams;
    }

    /**
     * @param array<string, mixed>|null $query
     */
    public function setQuery(?array $query = null): void
    {
        $this->queryParams = $query ?: [];
        $this->hrefCache   = null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getQuery(): array
    {
        return $this->queryParams;
    }

    public function setRouteResult(RouteResult $routeResult): void
    {
        $this->routeResult = $routeResult;
    }

    public function getRouteResult(): ?RouteResult
    {
        return $this->routeResult;
    }

    public function getUrlHelper(): ?UrlHelper
    {
        return $this->urlHelper;
    }

    public function setUrlHelper(UrlHelper $urlHelper): void
    {
        $this->urlHelper = $urlHelper;
    }
}
