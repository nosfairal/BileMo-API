<?php


namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class PaginationFactory
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function createCollection(QueryBuilder $query, Request $request, $route, array $routeParams = [], int $maxPerPage = 10): PaginatedCollection
    {
        $page = (int) $request->query->get('page',1);

        $adapter = new QueryAdapter($query, false);
        $pagerfanta = new Pagerfanta($adapter);

        $this->setPagerParameters($pagerfanta, $page, $maxPerPage);

        $results = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $results[] = $result;
        }

        $paginatedCollection = new PaginatedCollection(
            $results,
            $pagerfanta->getNbResults(),
            $page
        );

        if (!empty($paginatedCollection)) {
            $this->addLink($paginatedCollection, $route, $routeParams, $pagerfanta, $page);
        }

        return $paginatedCollection;
    }

    private function setPagerParameters($pagerfanta, $page, $maxPerPage): void
    {
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);
    }

    private function addLink($paginatedCollection, $route, $routeParams, $pagerfanta, $page)
    {
        $createLinkUrl = function ($targetPage) use ($route, $routeParams) {
            return $this->router->generate($route, array_merge(
                $routeParams,
                array('page' => $targetPage)
            ));
        };

        $paginatedCollection->addLink('self', $createLinkUrl($page));
        $paginatedCollection->addLink('first', $createLinkUrl(1));
        $paginatedCollection->addLink('last', $createLinkUrl($pagerfanta->getNbPages()));

        if ($pagerfanta->hasNextPage()) {
            $paginatedCollection->addLink('next', $createLinkUrl($pagerfanta->getNextPage()));
        }
        if ($pagerfanta->hasPreviousPage()) {
            $paginatedCollection->addLink('prev', $createLinkUrl($pagerfanta->getPreviousPage()));
        }
    }
}