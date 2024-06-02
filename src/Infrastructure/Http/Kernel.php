<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Infrastructure\Http\Message\Request;
use App\Infrastructure\Http\Message\Response;
use App\Infrastructure\Listener\Events\KernelTerminateEvent;
use App\Infrastructure\Listener\Events\RequestEvent;
use App\Infrastructure\Listener\ListenerService;
use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Kernel
{
    private ContainerInterface $container;
    private Router $router;

    public function __construct(
        private string $projectDir,
        private bool $isDebug = true,
    ) {
    }

    public function boot(): void
    {
        $this->configureContainer();
        $this->configureRoutes();
    }

    public function handle(Request $request): Response
    {
        try {
            [[$classFQCN, $classMethod], $vars] = $this->router->getHandler($request->getMethod(), $request->getUri());
            /** @var object $classInstance */
            $classInstance = $this->container->get($classFQCN);

            /** @var ListenerService $eventDispatcher */
            $eventDispatcher = $this->container->get(ListenerService::class);
            $eventDispatcher->dispatch(new RequestEvent($request));

            $response = $classInstance->$classMethod($request, ...$vars);
            if (!$response instanceof Response) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s expected as return type of controller %s method %s',
                        Response::class,
                        $classFQCN,
                        $classMethod,
                    ),
                );
            }
            return $response;
        } catch (\Throwable $e) {
            if (!$this->isDebug) {
                return new Response('Internal error', 500);
            }
            throw $e;
        }
    }

    public function terminate(): void
    {
        /** @var ListenerService $eventDispatcher */
        $eventDispatcher = $this->container->get(ListenerService::class);
        $eventDispatcher->dispatch(new KernelTerminateEvent());
    }

    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    private function configureRoutes(): void
    {
        $routesPath = $this->projectDir . '/config/routes.php';
        /** @var Dispatcher $dispatcher */
        $dispatcher = include $routesPath;
        $this->router = new Router($dispatcher);
    }

    private function configureContainer(): void
    {
        $cachePath = $this->projectDir . '/var/cache/container.php';
        $cache = new ConfigCache($cachePath, false);
        if (!$cache->isFresh() || $this->isDebug || !($container = include $cachePath) instanceof ContainerInterface) {
            $container = new ContainerBuilder();
            $fileLocator = new FileLocator($this->projectDir);
            $loaderResolver = new LoaderResolver([
                new YamlFileLoader($container, $fileLocator),
                new GlobFileLoader($container, $fileLocator)
            ]);
            $delegatingLoader = new DelegatingLoader($loaderResolver);

            $delegatingLoader->load('config/services.yaml');
            $delegatingLoader->load('src/**/Di/*.{php,xml,yaml,yml}', 'glob');
            $delegatingLoader->load('src/**/**/Di/*.{php,xml,yaml,yml}', 'glob');

            $container->set('kernel', $this);
            $container->setParameter('kernel.srcDir', $this->projectDir . '/src');
            $container->compile(true);

            $dumper = new PhpDumper($container);
            /** @var string $containerString */
            $containerString = $dumper->dump();
            $cache->write($containerString, $container->getResources());
        }
        $this->container = $container;
    }
}
