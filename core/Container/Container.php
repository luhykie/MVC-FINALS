<?php

declare(strict_types=1);

namespace Core\Container;

class Container
{
    private array $bindings = [];

    public function bind(string $key, callable $factory): void
    {
        $this->bindings[$key] = $factory;
    }

    public function make(string $key): mixed
    {
        if (!isset($this->bindings[$key])) {
            throw new \RuntimeException("No binding registered for {$key}.");
        }

        return $this->bindings[$key]();
    }
}
