<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Message;

class Request
{
    private string $deviceId;

    /**
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $server
     */
    public function __construct(
        public array $query = [],
        public array $post = [],
        public array $cookies = [],
        public array $files = [],
        public array $server = [],
    ) {
    }

    public function getUri(): string
    {
        return $this->server['REQUEST_URI'];
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getIpLong(): int
    {
        $ip = ip2long($this->server['REMOTE_ADDR'] ?? '');
        if ($ip === false) {
            throw new \InvalidArgumentException('Invalid ip address');
        }
        return $ip; // skip possible problems
    }

    public function setDeviceId(string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }
}
