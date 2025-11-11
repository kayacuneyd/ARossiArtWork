<?php

declare(strict_types=1);

namespace App\Support;

class View
{
    public static function render(string $template, array $data = [], string $layout = 'layouts/app.php'): void
    {
        $templatePath = self::viewPath($template);
        $layoutPath = self::viewPath($layout);

        if (!file_exists($templatePath)) {
            throw new \RuntimeException("View template not found: {$template}");
        }

        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout not found: {$layout}");
        }

        extract($data, EXTR_SKIP);
        $content = static function () use ($templatePath, $data): string {
            extract($data, EXTR_SKIP);
            ob_start();
            include $templatePath;
            return (string) ob_get_clean();
        };

        $body = $content();
        include $layoutPath;
    }

    public static function fragment(string $template, array $data = []): string
    {
        $templatePath = self::viewPath($template);
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("View fragment not found: {$template}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $templatePath;
        return (string) ob_get_clean();
    }

    private static function viewPath(string $template): string
    {
        $template = str_replace('\\', DIRECTORY_SEPARATOR, $template);

        if (!str_ends_with($template, '.php')) {
            $template = str_replace('.', DIRECTORY_SEPARATOR, $template) . '.php';
        }

        return base_path('src/Views/' . ltrim($template, '/'));
    }
}
