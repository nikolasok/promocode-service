<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Http\Exception\HttpMethodNotAllowedException;
use App\Infrastructure\Http\Exception\HttpRouteNotFoundException;
use FastRoute\Dispatcher;

class Router
{
    public function __construct(private Dispatcher $dispatcher)
    {
    }

    /**
     * @return array{0: array{0: class-string, 1: string}, 1: array<int|bool|string|float>}
     * @throws HttpRouteNotFoundException
     * @throws HttpMethodNotAllowedException
     */
    public function getHandler(string $httpMethod, string $uri): array
    {
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new HttpRouteNotFoundException();
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new HttpMethodNotAllowedException();
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                break;
        }

        if (!isset($handler) || !isset($vars)) {
            throw new \InvalidArgumentException('Route handler and vars are expected');
        }

        return [$handler, $vars];
    }
}
