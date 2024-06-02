<?php

declare(strict_types=1);

namespace App\Infrastructure\Template;

class TemplateService
{
    public function __construct(private string $projectPath)
    {
    }

    /**
     * @param array<mixed> $vars
     */
    public function render(string $path, array $vars = []): string
    {
        ob_start();
        extract($vars);
        include $this->projectPath . '/' . $path;
        return ob_get_clean() ?: '';
    }
}
