<?php

declare(strict_types=1);

namespace App\Infrastructure\Lock;

use App\Infrastructure\Database\Database;

class LockService
{
    public function __construct(private Database $database)
    {
    }

    public function lock(string $namespace, string $key): Lock
    {
        $lock = new Lock($this->database, $namespace, $key);
        $lock->acquire();
        return $lock;
    }
}
