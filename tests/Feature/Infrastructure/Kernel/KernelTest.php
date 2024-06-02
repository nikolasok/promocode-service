<?php

declare(strict_types=1);

namespace App\Tests\Feature\Infrastructure\Kernel;

use App\Infrastructure\Test\WebTestCase;
use App\Kernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class KernelTest extends WebTestCase
{
    public function testSome(): void
    {
        $kernel = new Kernel();
        $kernel->boot();

        self::assertInstanceOf(ContainerInterface::class, $kernel->getContainer());
    }
}
