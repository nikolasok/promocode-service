<?php

declare(strict_types=1);

namespace App\Infrastructure\Session;

class NativeSessionService
{
    public function start(): void
    {
        if (!isset($_SESSION)) {
            if (\PHP_SAPI === 'cli') {
                $_SESSION = array();
            } else {
                if (!session_start([
                    'cookie_lifetime' => 31536000,
                    'gc_maxlifetime' => 31536000,
                ])) { // I think it's ok for current task
                    throw new \RuntimeException('Failed to start the session.');
                }
            }
        }

    }

    public function set(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    public function has(string $name): bool
    {
        return isset($_SESSION[$name]);
    }

    public function get(string $name): mixed
    {
        return $_SESSION[$name];
    }
}
