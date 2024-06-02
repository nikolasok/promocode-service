<?php

declare(strict_types=1);

namespace App\Infrastructure\Listener;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ListenerService
{
    /**
     * @var array<string, callable[]>
     */
    private array $eventMap = [];

    /**
     * @param iterable<object> $handlers
     *
     * @throws \ReflectionException
     */
    public function __construct(
        #[TaggedIterator('app.listener')]
        iterable $handlers,
    ) {
        foreach ($handlers as $handler) {
            if (!\is_callable($handler)) {
                throw new \InvalidArgumentException('Class ' . $handler::class . 'must implement __invoke method');
            }
            $reflection = new \ReflectionClass($handler);
            $method = $reflection->getMethod('__invoke');
            $parameters = $method->getParameters();
            if (empty($parameters)) {
                throw new \ArgumentCountError('Expected minimum one parameter in listener' . $handler::class);
            }
            $parameter = $parameters[0];
            $parameterClass = $parameter->getType();
            if ($parameterClass === null || !$parameterClass instanceof \ReflectionNamedType) {
                throw new \InvalidArgumentException('Unexpected type in listener' . $handler::class);
            }
            $fqcn = $parameterClass->getName();
            $this->eventMap[$fqcn][] = $handler;
        }
    }

    /**
     * @template T of object
     * @param T&object $event
     * @return T&object
     */
    public function dispatch(object $event): object
    {
        $eventClass = $event::class;
        if (isset($this->eventMap[$eventClass])) {
            foreach ($this->eventMap[$eventClass] as $handler) {
                $handler($event);
            }
        }

        return $event;
    }
}
