<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Message;

class RedirectResponse extends Response
{
    public function __construct(string $url, int $status = 302, array $headers = [])
    {
        parent::__construct('', $status, $headers);
        $this->addHeader('location', $url);
    }
}
