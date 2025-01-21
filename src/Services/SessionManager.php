<?php

namespace Enzo\P5OcBlog\Services;

class SessionManager
{
    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }
    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }
    public static function getAll(): array
    {
        return $_SESSION;
    }
    public static function clearKey(string $key): void
    {
        unset($_SESSION[$key]);
    }
    public static function clear(): void
    {
        session_unset();
    }
    public static function getFilteredData(): array
    {
        return [
            'user_id' => self::get('user_id'),
            'username' => self::get('username'),
        ];
    }
}