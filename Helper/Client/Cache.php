<?php

declare(strict_types=1);

namespace M2E\TikTokShop\Helper\Client;

class Cache
{
    public static function isApcAvailable(): bool
    {
        return extension_loaded('apc') && ini_get('apc.enabled');
    }

    public static function isMemcachedAvailable(): bool
    {
        return (extension_loaded('memcache') || extension_loaded('memcached')) &&
            (class_exists('Memcache', false) || class_exists('Memcached', false));
    }

    public static function isRedisAvailable(): bool
    {
        return extension_loaded('redis') && class_exists('Redis', false);
    }

    public static function isZendOpcacheAvailable(): bool
    {
        return function_exists('opcache_get_status');
    }
}
