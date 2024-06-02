<?php

declare(strict_types=1);

namespace App\Infrastructure\Test;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Http\Message\Request;
use App\Infrastructure\Http\Message\Response;
use App\Kernel;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class WebTestCase extends TestCase
{
    private Kernel $kernel;

    protected function setUp(): void
    {
        $this->kernel = new Kernel(isDebug: (bool)($_ENV['APP_DEBUG'] ?? 0));
        $this->kernel->boot();
        $this->getDb()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->getDb()->rollBack();
    }

    /**
     * @param array<string> $post
     */
    public function request(string $method, string $uri, array $post = []): Response
    {
        $request = new Request([], $post, [], [], [
            'REQUEST_URI' => $uri,
            'REQUEST_METHOD' => $method,
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $response = $this->kernel->handle($request);
        return $response;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->kernel->getContainer();
    }

    protected function getDb(): Database
    {
        /** @var Database $instance */
        $instance = $this->getContainer()->get(Database::class);
        return $instance;
    }
}
