<?php

declare(strict_types=1);

namespace Core\Container;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

final class Container
{
    // Stores custom rules for creating classes or services.
    private array $bindings = [];

    public function bind(string $key, callable|string $factory): void
    {
        // Register how a class/service should be created.
        $this->bindings[$key] = $factory;
    }

    public function has(string $key): bool
    {
        // The container can make it if it is bound or if the class exists.
        return isset($this->bindings[$key]) || class_exists($key);
    }

    public function make(string $key): mixed
    {
        // Use a registered binding first when one exists.
        if (isset($this->bindings[$key])) {
            $factory = $this->bindings[$key];

            // Callable bindings receive the container so they can ask for dependencies.
            if (is_callable($factory)) {
                return $factory($this);
            }

            // String bindings point to a class name that should be built.
            return $this->build($factory);
        }

        // If there is no binding, try building the class directly.
        if (class_exists($key)) {
            return $this->build($key);
        }

        throw new RuntimeException("No binding found for {$key}");
    }

    private function build(string $class): object
    {
        // Reflection lets the container inspect the class constructor.
        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new RuntimeException("Class {$class} is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        // Classes without constructors can be created normally.
        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            // If the parameter is another class, ask the container to make that class too.
            if ($type instanceof ReflectionNamedType && ! $type->isBuiltin()) {
                $dependencies[] = $this->make($type->getName());
                continue;
            }

            // Use default values for optional parameters.
            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new RuntimeException(
                "Cannot resolve parameter {$parameter->getName()} in {$class}"
            );
        }

        // Create the class with all resolved constructor dependencies.
        return $reflection->newInstanceArgs($dependencies);
    }
}
