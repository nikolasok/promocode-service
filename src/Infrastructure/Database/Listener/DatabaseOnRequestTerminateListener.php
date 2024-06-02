<?php

declare(strict_types=1);

namespace App\Infrastructure\Database\Listener;

use App\Infrastructure\Database\Database;
use App\Infrastructure\Listener\Events\KernelTerminateEvent;

class DatabaseOnRequestTerminateListener
{
    public function __construct(private Database $database)
    {
    }

    public function __invoke(KernelTerminateEvent $event): void
    {
        $this->database->disconnect();
    }
}
