<?php

declare(strict_types=1);

namespace Core\Container;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    // Mo-store sa custom rules para mag-create ug classes or services.
    private array $bindings = [];

    public function bind(string $key, callable|string $factory): void
    {
        // I-register kung giunsa pag-create ang class/service.
        $this->bindings[$key] = $factory;
    }

    public function has(string $key): bool
    {
        // Maka-make ang container kung bound siya or nag-exist ang class.
        return isset($this->bindings[$key]) || class_exists($key);
    }

    public function make(string $key): mixed
    {
        // Gamiton una ang registered binding kung naa.
        if (isset($this->bindings[$key])) {
            $factory = $this->bindings[$key];

            // Callable bindings modawat sa container para makapangayo ug dependencies.
            if (is_callable($factory)) {
                return $factory($this);
            }

            // String bindings mo-point sa class name nga i-build.
            return $this->build($factory);
        }

        // Kung walay binding, try i-build directly ang class.
        if (class_exists($key)) {
            return $this->build($key);
        }

        throw new RuntimeException("No binding found for {$key}");
    }

    private function build(string $class): object
    {
        // Reflection motabang sa container mo-inspect sa class constructor.
        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Class {$class} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        // Classes nga walay constructors pwede i-create normally.
        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            // Kung ang parameter kay another class, ipamake pud sa container.
            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            // Gamiton ang default values para sa optional parameters.
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter {$parameter->getName()} in {$class}"
            );
        }

        // I-create ang class with all resolved constructor dependencies.
        return $reflection->newInstanceArgs($dependencies);
    }
}
