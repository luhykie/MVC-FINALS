<?php

declare(strict_types=1);

namespace Core\Container;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    private array $bindings = [];

    public function bind(string $key, callable|string $factory): void
    {
        $this->bindings[$key] = $factory;
    }

    public function has(string $key): bool
    {
        return isset($this->bindings[$key]) || class_exists($key);
    }

    public function make(string $key): mixed
    {
        if (isset($this->bindings[$key])) {
            $factory = $this->bindings[$key];

            if (is_callable($factory)) {
                return $factory($this);
            }

            return $this->build($factory);
        }

        if (class_exists($key)) {
            return $this->build($key);
        }

        throw new RuntimeException("No binding found for {$key}");
    }

    private function build(string $class): object
    {
        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Class {$class} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter {$parameter->getName()} in {$class}"
            );
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}