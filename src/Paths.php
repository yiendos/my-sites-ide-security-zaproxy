<?php

namespace Yiendos\MySitesIde\Security\Zaproxy;

use RuntimeException;

/**
 * Where this plugin's files live. Read-only assets (stubs, the example
 * config, scripts) ship inside the package, but anything the user creates -
 * contexts and reports - lives under the IDE's storage/plugins/zaproxy/, as
 * the package directory is replaced on every `composer update`.
 *
 * The IDE root comes from IDE_ROOT, which the my-sites-ide bootstrap sets
 * before any plugin command runs (and which docker-compose.yml interpolates).
 */
final class Paths
{
    public const STORAGE = 'storage/plugins/zaproxy';

    /**
     * The my-sites-ide project root
     *
     * @return string
     */
    public static function root(): string
    {
        $root = getenv('IDE_ROOT');

        if ($root === false || $root === '') {
            throw new RuntimeException('IDE_ROOT is not set - run this command through the my-sites-ide CLI.');
        }

        return rtrim($root, '/');
    }

    /**
     * A file within the user's contexts directory (<target>.zap-config.php)
     *
     * @param string $file
     * @return string
     */
    public static function contexts(string $file = ''): string
    {
        return self::storage('contexts', $file);
    }

    /**
     * A file within the reports directory, mounted into the container as /zap/wrk
     *
     * @param string $file
     * @return string
     */
    public static function reports(string $file = ''): string
    {
        return self::storage('reports', $file);
    }

    /**
     * A file shipped with this package, e.g. stubs/laravel
     *
     * @param string $file
     * @return string
     */
    public static function package(string $file = ''): string
    {
        return dirname(__DIR__) . ($file === '' ? '' : "/{$file}");
    }

    /**
     * An absolute path shown relative to the IDE root, for user-facing messages
     *
     * @param string $path
     * @return string
     */
    public static function relative(string $path): string
    {
        $root = self::root() . '/';

        return str_starts_with($path, $root) ? substr($path, strlen($root)) : $path;
    }

    /**
     * Creates the storage directory on first use - Docker would otherwise
     * create the reports bind mount itself, owned by root on Linux hosts
     *
     * @param string $directory
     * @param string $file
     * @return string
     */
    private static function storage(string $directory, string $file): string
    {
        $path = self::root() . '/' . self::STORAGE . "/{$directory}";

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $file === '' ? $path : "{$path}/{$file}";
    }
}
