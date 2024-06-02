<?php

declare(strict_types=1);

namespace App;

use App\Infrastructure\Http\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function __construct(?string $projectDir = null, bool $isDebug = true)
    {
        if ($projectDir === null) {
            $projectDir = \dirname(__DIR__);
        }
        parent::__construct($projectDir, $isDebug);
    }
}
