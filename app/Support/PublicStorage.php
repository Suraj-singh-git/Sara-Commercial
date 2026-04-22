<?php

namespace App\Support;

/**
 * Builds URLs for files on the public disk using the current request host when available,
 * so /storage/... works when visiting via 127.0.0.1 or localhost even if APP_URL differs.
 */
final class PublicStorage
{
    public static function url(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        $host = '';
        try {
            $host = (string) request()->getSchemeAndHttpHost();
        } catch (\Throwable) {
            $host = '';
        }

        if ($host !== '' && $host !== 'http://' && $host !== 'https://') {
            return $host.'/storage/'.$path;
        }

        return rtrim((string) config('app.url'), '/').'/storage/'.$path;
    }
}
