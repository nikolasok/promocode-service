<?php

declare(strict_types=1);

namespace App\Infrastructure\Listener\Events;

use App\Infrastructure\Http\Message\Request;

class RequestEvent
{
    public function __construct(private Request $request)
    {
    }

    public function getRequest(): Request
    {
        return $this->request;
    }
}
