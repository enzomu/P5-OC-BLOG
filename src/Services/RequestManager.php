<?php

namespace Enzo\P5OcBlog\Services;

class RequestManager
{
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function getPost(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public function getGet(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public function getSession(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function setSession(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function unsetSession(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
