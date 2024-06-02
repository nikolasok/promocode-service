<?php

declare(strict_types=1);

namespace App\Infrastructure\Session\Listener;

use App\Infrastructure\Listener\Events\RequestEvent;
use App\Infrastructure\Session\NativeSessionService;
use Ramsey\Uuid\Uuid;

class SessionOnRequestListener
{
    public function __construct(private NativeSessionService $sessionService)
    {
    }

    public function __invoke(RequestEvent $event): void
    {
        $this->sessionService->start();

        if (!$this->sessionService->has('device_id')) {
            $deviceId = Uuid::uuid4()->toString();
            $this->sessionService->set('device_id', $deviceId);
        } else {
            /** @var string $deviceId */
            $deviceId = $this->sessionService->get('device_id');
        }
        $event->getRequest()->setDeviceId($deviceId);
    }
}
