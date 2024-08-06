<?php

namespace think\ai\agent\tool;

use ArrayAccess;
use ArrayIterator;
use Exception;
use IteratorAggregate;
use ReturnTypeWillChange;
use Traversable;

class Args implements ArrayAccess, IteratorAggregate
{
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function get($offset, $default = null)
    {
        if (is_null($default) && !isset($this->data[$offset])) {
            throw new Exception('Missing required parameter: ' . $offset);
        }
        return $this->data[$offset] ?? $default;
    }

    #[ReturnTypeWillChange]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {

    }

    public function offsetUnset(mixed $offset): void
    {

    }
}
